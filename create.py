#!/usr/bin/python

import sys
import os
import shutil

if len(sys.argv) < 2:
    print "you must enter a project name"
    exit()
    
name = sys.argv[1]

opts = None
if len(sys.argv) > 2:
    print "reading options"
    opts = sys.argv[2]

#check if project already exists
if not os.path.exists(name):    
    print "creating project", name
    shutil.copytree('.template', name, symlinks=True)
    os.system('sed s/NAME/'+name+'/g ./.template/config/config.php > '+name+'/config/config.php')
    os.system('sed s/NAME/'+name+'/g ./.template/test/Test.php > '+name+'/test/Test.php')

#check for options
if opts == "+u":
    print "creating uploads dir"
    os.system('mkdir '+name+'/uploads && sudo chown www-data:www-data '+name+'/uploads'); 

#check if we need to add a record to hosts
hosts_record = "127.0.0.1 "+name
if not ((hosts_record+'\n') in open("/etc/hosts").readlines()) and\
    not (hosts_record in open("/etc/hosts").readlines()) :
    print "adding new record to hosts"
    os.system('su -c "echo '+hosts_record+' >>/etc/hosts"')


if (not os.path.exists('/etc/apache2/sites-available/'+name+'.site')):
    print "creating new apache2 file"
    f = open(".zilant/template.site")
    apachesite = f.read()
    f.close()
    apachesite = apachesite.replace("$name", name)
    apachesite = apachesite.replace("$loc", os.getcwd())
    f = open(name+'.site','w')
    f.write(apachesite)
    f.close()

    os.system('su -c "mv '+name+'.site /etc/apache2/sites-available && a2ensite '+name+'.site && service apache2 reload" ')

# we're doing it every time for now
print "creating database"
os.system('sed s/NAME/'+name+'/g .zilant/createdb.sql | mysql -uroot -p')

print "Uploading deltas"
os.system('cd ./'+name+'&& ./upload')
