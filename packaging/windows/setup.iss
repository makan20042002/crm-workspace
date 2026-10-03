#define ProductName "CRM Workspace Offline"
#ifndef AppVersion
  #define AppVersion "10.1.3"
#endif
[Setup]
AppId={{A7D81AD4-445E-49D9-B44D-9FC4E6027681}
AppName={#ProductName}
AppVersion={#AppVersion}
DefaultDirName={commonappdata}\CRMWorkspace
DefaultGroupName=CRM Workspace
PrivilegesRequired=admin
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
OutputDir={#OutputDir}
OutputBaseFilename=crm-workspace-offline-{#AppVersion}-setup
Compression=lzma2/ultra64
SolidCompression=yes
DisableProgramGroupPage=yes
[Tasks]
Name: "firewall"; Description: "Allow other devices on the private LAN to connect"; Flags: checkedonce
[Files]
Source: "{#SourceDir}\app\*"; DestDir: "{app}\app"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "{#SourceDir}\runtime\*"; DestDir: "{app}\runtime"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "{#SourceDir}\installer\*"; DestDir: "{app}\installer"; Flags: ignoreversion recursesubdirs createallsubdirs
[Icons]
Name: "{group}\Open CRM Workspace"; Filename: "powershell.exe"; Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\installer\open-crm.ps1"" -Root ""{app}"""
[Run]
Filename: "powershell.exe"; Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\installer\configure.ps1"" -Root ""{app}"" -WebPort {code:WebPort} -DbPort 3307 -BackupDir ""{code:BackupDir}"" {code:FirewallArg}"; Flags: runhidden waituntilterminated
[UninstallRun]
Filename: "powershell.exe"; Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\installer\uninstall.ps1"" -Root ""{app}"""; Flags: runhidden waituntilterminated; RunOnceId: "StopCRMWorkspace"
[Code]
var PortPage: TInputQueryWizardPage; BackupPage: TInputDirWizardPage;
procedure InitializeWizard; begin PortPage:=CreateInputQueryPage(wpSelectDir,'Web server','Choose the LAN web-server port','Use an unused TCP port.');PortPage.Add('Port:',False);PortPage.Values[0]:='8088';BackupPage:=CreateInputDirPage(PortPage.ID,'Daily backups','Choose the backup folder','Automatic backups are kept here; the latest 14 are retained.',False,'');BackupPage.Add('Backup folder:');BackupPage.Values[0]:=ExpandConstant('{commonappdata}\CRMWorkspace\data\backups');end;
function WebPort(Param:String):String;begin Result:=PortPage.Values[0];end;
function BackupDir(Param:String):String;begin Result:=BackupPage.Values[0];end;
function FirewallArg(Param: String): String; begin if WizardIsTaskSelected('firewall') then Result := '-Firewall' else Result := ''; end;
procedure CurUninstallStepChanged(CurUninstallStep: TUninstallStep); begin if CurUninstallStep=usPostUninstall then begin if SuppressibleMsgBox('Keep database, uploads, configuration, and backups? Choose No to permanently remove all data.',mbConfirmation,MB_YESNO,IDYES)=IDNO then DelTree(ExpandConstant('{app}'),True,True,True); end; end;
