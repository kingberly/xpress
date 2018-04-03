'''
Created on 2014/9/1

@author: Taro
'''

import shutil


strV120 = '*/5 * * * * root /usr/igs/updateRRDs.sh'
strV130 = '*/5 * * * * root /usr/igs/scripts/updateRRDs.sh'
def loadCrontab():
    ct = "".join([line for line in open("/etc/crontab", "r")])
    return ct

def writeCrontab(s):
    shutil.move('/etc/crontab', '/etc/crontab.bak')
    f = open('/etc/crontab', 'w')
    f.write(s)
    f.close()
    
if __name__ == '__main__':
    s = loadCrontab()
    needCopy = False
    # Remove mistake str
    if(s.find(strV120) != -1):
        s = s.replace(strV120, '')
        needCopy = True
        
    # Add needed str
    if(s.find(strV130) == -1):
        s = s + strV130+'\n'
        needCopy = True
    
    if(needCopy):
        writeCrontab(s)
        

      
