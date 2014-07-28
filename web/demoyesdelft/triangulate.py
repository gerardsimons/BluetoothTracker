#!C:\Anaconda\python.exe -u
#!/usr/bin/env python

import cgi
import cgitb; cgitb.enable()  # for troubleshooting
from phpserialize import loads as php_loads
from numpy import zeros as np_zeros, array as np_array
from time import clock as time_clock
from triangulation import triangulate
import sys
import urllib

if len(sys.argv) > 1:
	s = urllib.unquote(sys.argv[1]).decode('utf8')
	try:
		stream = php_loads(s)
		
		set = np_zeros((len(stream), 5))
		for i in stream:
			measurement = stream[i]
			set[i, :] = np_array([float(val) for (key, val) in measurement.iteritems()])
		pos = triangulate(set)
		print pos
		""" Code below is for when the input is the entire series of measurement sets,
			but in the new version of triangulate.php triangulation is done right after 
			a set has been completed, because this is way more efficient
		for i in stream:
			series = stream[i]
			for j in series:
				set = series[j]
				_set = np_zeros((len(set), 5))
				for k in set:
					measurement = set[k]
					_set[k, :] = np_array([float(val) for (key, val) in measurement.iteritems()])
				start = time_clock()
				pos = triangulate(_set)
				stop = time_clock()
				print pos
				print """
	except ValueError:
		print "Something went wrong..."
else:
	print "Hello world!"