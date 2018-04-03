import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand

import os


defaultHost=['192.168.1.202', '22', 'ivedasuper', '1qazxdr56yhN']

def getFile(host,fileExt,localExt):
  #localExt = os.getcwd()+"/"+fileExt
  ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
  sftp=ssh.open_sftp()
  print "%s remote file:%s => local file:%s" %(host[0] ,fileExt, localExt)
  sftp.get(fileExt,localExt)
  sftp.close()
  ssh.close()

def putFile(host,localExt,targetExt):
  #targetExt = os.getcwd()+"/"+fileExt
  ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
  sftp=ssh.open_sftp()
  print "local file:%s => %s remote file:%s" %(localExt, host[0] ,targetExt)
  sftp.put(localExt,targetExt) #local, remote
  sftp.close()
  ssh.close() 
def createSSHClient(server, port, user, password):
  client = paramiko.SSHClient()
  client.load_system_host_keys()
  client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
  #client.set_missing_host_key_policy(paramiko.WarningPolicy())
  client.connect(server, port, user, password)
  return client
#
# Main function
#
if (len(sys.argv)<=2):
  sys.exit("Usage:(getfile) python sshgetFile.py name:pwd@TargetIP/filepath localpath \n     (putfile) python sshgetFile.py localpath name:pwd@TargetIP/filepath \n")

transferTYPE="get"
if "@" in str(sys.argv[1]):
  transferTYPE="get"
  param1=str(sys.argv[1])
  localPath=str(sys.argv[2])
  #is_path_exists_or_creatable() #python3 only
elif "@" in str(sys.argv[2]):  #putfile
  transferTYPE="put"
  param1=str(sys.argv[2])
  localPath=str(sys.argv[1])
  if not os.path.exists(localPath) :
			sys.exit("{0} Not Exist!!".format(localPath)) 
 
##### getFile, parse param1, localpath
defaultHost[2]=param1.split(":")[0]
if (defaultHost[2]==""):
  sys.exit("No account!")

paramAuth=param1.split("@")[0].strip()
defaultHost[3]=paramAuth.lstrip(defaultHost[2]+":")
if (defaultHost[3]==""):
  sys.exit("No Password!")

defaultHost[0]=param1.split("/")[0].lstrip(paramAuth).lstrip("@")
if (defaultHost[0]==""):
  sys.exit("No Target IP!")


filePath=param1.split("@")[1].lstrip(defaultHost[0])
if (filePath==""):
  sys.exit("No Target FilePath!")

#DEBUG
#print '{0} {1} {2} {3} {4} local={5}\n'.format(defaultHost[0],defaultHost[1],defaultHost[2],defaultHost[3],filePath,localPath)

if (transferTYPE == "get") :
  try:
    getFile(defaultHost,filePath,localPath)          
  except Exception as e:
    print str(e)
elif (transferTYPE == "put") :
  try:
    putFile(defaultHost,localPath,filePath)          
  except Exception as e:
    print str(e)