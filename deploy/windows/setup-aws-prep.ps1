param([switch]$DoLogin)

$ErrorActionPreference = 'Stop'
$ProfileName = 'attendify'
$Region = 'ap-southeast-2'
$RoleName = 'AttendifyEC2Role'
$InstanceProfileName = 'AttendifyEC2Profile'
$SgName = 'attendify-app-sg'
$LtName = 'attendify-lt'
$SsmPolicy = 'arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore'
$Root = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$TempDir = Join-Path $env:TEMP 'attendify-deploy-prep'
New-Item -ItemType Directory -Force $TempDir | Out-Null

$Aws = (Get-Command aws -ErrorAction SilentlyContinue).Source
if (-not $Aws -and (Test-Path 'D:\AWSCLIV2\aws.exe')) { $Aws = 'D:\AWSCLIV2\aws.exe' }
if (-not $Aws) { throw 'AWS CLI tidak ditemukan. Pasang di D:\AWSCLIV2 atau tambahkan aws ke PATH.' }

function Invoke-Aws {
    param([string[]]$Params)
    $result = & $Aws @Params --profile $ProfileName 2>&1
    if ($LASTEXITCODE -ne 0) { throw "Gagal: aws $($Params -join ' ') -> $result" }
    return ($result | Out-String).Trim()
}

function Get-OrNull {
    param([string[]]$Params)
    $result = & $Aws @Params --profile $ProfileName 2>$null
    if ($LASTEXITCODE -ne 0) { return $null }
    $text = ($result | Out-String).Trim()
    if (-not $text -or $text -eq 'None') { return $null }
    return $text
}

Write-Host '=== Attendify AWS prep (tanpa menjalankan instance) ===' -ForegroundColor Cyan

$account = Get-OrNull @('sts','get-caller-identity','--query','Account','--output','text')
if (-not $account) {
    Write-Host 'Kredensial expired. Jalankan: aws login --region ap-southeast-2 --profile attendify' -ForegroundColor Yellow
    if ($DoLogin) {
        & $Aws login --region $Region --profile $ProfileName
        if ($LASTEXITCODE -ne 0) { throw 'Login gagal.' }
        $account = Invoke-Aws @('sts','get-caller-identity','--query','Account','--output','text')
    } else { exit 1 }
}
Write-Host "Akun: $account | Region: $Region | Profil: $ProfileName"

$plan = Invoke-Aws @('freetier','get-account-plan-state','--region','us-east-1','--query','[accountPlanType,accountPlanRemainingCredits.amount]','--output','text')
Write-Host "Paket: $plan"

$vpcId = Invoke-Aws @('ec2','describe-vpcs','--filters','Name=is-default,Values=true','--query','Vpcs[0].VpcId','--output','text')
$amiId = Invoke-Aws @('ssm','get-parameter','--name','/aws/service/ami-amazon-linux-latest/al2023-ami-kernel-default-x86_64','--region',$Region,'--query','Parameter.Value','--output','text')
Write-Host "VPC: $vpcId | AMI: $amiId"

$trustPath = Join-Path $TempDir 'trust-policy.json'
$trust = '{"Version":"2012-10-17","Statement":[{"Effect":"Allow","Principal":{"Service":"ec2.amazonaws.com"},"Action":"sts:AssumeRole"}]}'
[IO.File]::WriteAllText($trustPath, $trust, (New-Object Text.UTF8Encoding($false)))

$roleArn = Get-OrNull @('iam','get-role','--role-name',$RoleName,'--query','Role.Arn','--output','text')
if (-not $roleArn) {
    $roleArn = Invoke-Aws @('iam','create-role','--role-name',$RoleName,'--assume-role-policy-document',"file://$($trustPath -replace '\\','/')",'--description','Role EC2 Attendify untuk SSM','--query','Role.Arn','--output','text')
    Write-Host "IAM role dibuat: $roleArn" -ForegroundColor Green
} else { Write-Host "IAM role sudah ada: $roleArn" }

$attached = Get-OrNull @('iam','list-attached-role-policies','--role-name',$RoleName,'--query','AttachedPolicies[].PolicyArn','--output','text')
if (-not $attached -or $attached -notmatch 'AmazonSSMManagedInstanceCore') {
    Invoke-Aws @('iam','attach-role-policy','--role-name',$RoleName,'--policy-arn',$SsmPolicy) | Out-Null
    Write-Host 'Kebijakan SSM dilekatkan.' -ForegroundColor Green
}

$profileArn = Get-OrNull @('iam','get-instance-profile','--instance-profile-name',$InstanceProfileName,'--query','InstanceProfile.Arn','--output','text')
if (-not $profileArn) {
    $profileArn = Invoke-Aws @('iam','create-instance-profile','--instance-profile-name',$InstanceProfileName,'--query','InstanceProfile.Arn','--output','text')
    Write-Host "Instance profile dibuat: $profileArn" -ForegroundColor Green
}
$profileRoles = Get-OrNull @('iam','get-instance-profile','--instance-profile-name',$InstanceProfileName,'--query','InstanceProfile.Roles[].RoleName','--output','text')
if (-not $profileRoles -or $profileRoles -notmatch [regex]::Escape($RoleName)) {
    Invoke-Aws @('iam','add-role-to-instance-profile','--instance-profile-name',$InstanceProfileName,'--role-name',$RoleName) | Out-Null
    Write-Host 'Role ditambahkan ke instance profile.' -ForegroundColor Green
}

$sgId = Get-OrNull @('ec2','describe-security-groups','--region',$Region,'--filters',"Name=group-name,Values=$SgName",'--query','SecurityGroups[0].GroupId','--output','text')
if (-not $sgId) {
    $sgId = Invoke-Aws @('ec2','create-security-group','--group-name',$SgName,'--description','Attendify web app (HTTP/HTTPS publik, kelola via SSM)','--vpc-id',$vpcId,'--region',$Region,'--query','GroupId','--output','text')
    Write-Host "Security group dibuat: $sgId" -ForegroundColor Green
} else { Write-Host "Security group sudah ada: $sgId" }
$ports = Get-OrNull @('ec2','describe-security-groups','--region',$Region,'--group-ids',$sgId,'--query','SecurityGroups[0].IpPermissions[].FromPort','--output','text')
foreach ($port in @(80,443)) {
    if (-not $ports -or $ports -notmatch "(^|\s)$port(\s|$)") {
        Invoke-Aws @('ec2','authorize-security-group-ingress','--group-id',$sgId,'--protocol','tcp','--port',"$port",'--cidr','0.0.0.0/0','--region',$Region) | Out-Null
        Write-Host "Port $port dibuka." -ForegroundColor Green
    }
}

$ltId = Get-OrNull @('ec2','describe-launch-templates','--region',$Region,'--launch-template-name',$LtName,'--query','LaunchTemplates[0].LaunchTemplateId','--output','text')
if (-not $ltId) {
    $userDataPath = Join-Path $Root 'deploy\user-data.sh'
    if (-not (Test-Path $userDataPath)) { throw "Bootstrap tidak ditemukan: $userDataPath" }
    $userData = [Convert]::ToBase64String([IO.File]::ReadAllBytes($userDataPath))
    $data = @{
        ImageId = $amiId
        InstanceType = 't3.micro'
        SecurityGroupIds = @($sgId)
        IamInstanceProfile = @{ Name = $InstanceProfileName }
        CreditSpecification = @{ CpuCredits = 'standard' }
        MetadataOptions = @{ HttpTokens = 'required'; HttpPutResponseHopLimit = 1 }
        UserData = $userData
        BlockDeviceMappings = @(@{ DeviceName = '/dev/xvda'; Ebs = @{ VolumeSize = 20; VolumeType = 'gp3'; DeleteOnTermination = $true } })
        TagSpecifications = @(@{ ResourceType = 'instance'; Tags = @(@{ Key = 'Project'; Value = 'Attendify' }, @{ Key = 'Env'; Value = 'demo' }) })
    } | ConvertTo-Json -Depth 8
    $dataPath = Join-Path $TempDir 'lt-data.json'
    [IO.File]::WriteAllText($dataPath, $data, (New-Object Text.UTF8Encoding($false)))
    $ltId = Invoke-Aws @('ec2','create-launch-template','--launch-template-name',$LtName,'--version-description','AL2023 PHP8.3 t3.micro','--launch-template-data',"file://$($dataPath -replace '\\','/')",'--region',$Region,'--query','LaunchTemplate.LaunchTemplateId','--output','text')
    Write-Host "Launch template dibuat: $ltId" -ForegroundColor Green
} else { Write-Host "Launch template sudah ada: $ltId" }

$instances = Invoke-Aws @('ec2','describe-instances','--region',$Region,'--query','length(Reservations[*].Instances[*])','--output','text')
Write-Host ''
Write-Host "Selesai. EC2 berjalan: $instances (tidak ada instance yang dinyalakan oleh script ini)." -ForegroundColor Cyan
Write-Host "Resource: $roleArn | $sgId | $ltId" -ForegroundColor Cyan
