import sys
import paramiko

#
# Utilities
#
clients = dict()

def connect(host):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(host[0], port=int(host[1]), username=host[2], password=host[3])
    #print str(host[0]) + " connected"
    return client

def execCommand(host, command):
    if not host[0] in clients.keys() or not clients[host[0]].get_transport().is_active(): # auto reconnect
        #   print 'Going to reconnect ' + hostname
        clients[host[0]] = connect(host)

    #print hostname + " is_active:" + str(self.clients[hostname].get_transport().is_active())

    stdin, stdout, stderr = clients[host[0]].exec_command(command)
    return stdin, stdout, stderr

def execSudoCommand(host, command):
    if not host[0] in clients.keys() or not clients[host[0]].get_transport().is_active(): # auto reconnect
        clients[host[0]] = connect(host)

    chan = clients[host[0]].get_transport().open_session()
    chan.get_pty()
    bufsize = 4096
    chan.exec_command("sudo " + command)
    stdin = chan.makefile('wb', bufsize)
    stdout = chan.makefile('rb', bufsize)
    stderr = chan.makefile_stderr('rb', bufsize)

    stdin.write(host[3]+'\n')
    stdin.flush()
    return stdin, stdout, stderr