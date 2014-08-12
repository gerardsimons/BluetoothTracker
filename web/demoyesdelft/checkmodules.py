#!C:\Anaconda\python.exe -u
#!/usr/bin/env python

import cgi
import cgitb; cgitb.enable()  # for troubleshooting

print "Content-Type: text/plain;charset=utf-8"
print

print "<html>"

print help('modules')

print "</html>"