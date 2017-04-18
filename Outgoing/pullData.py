#! /usr/bin/python

import csv
import json
import datetime as dt
import wget
import urllib2


def saveFile(fileName,Path,dataArr):
	'''
	save the data to some format.
	'''
	import csv
	
	#with open("%s%s.csv"%(Path,fileName),"w") as f:	
	#	f.write(",".join(dataArr[0].keys()) + "\n")
	#	for i in range(len(dataArr)):    		
	#		for row in zip(dataArr[i].values()):
	#			f.write(",".join(str(n) for n in row))
	#			f.write("\n")
	#f.close()
    
	#get Params from the dictionary
	f = open('%s%s.csv'%(Path,fileName),'wb')
	w = csv.DictWriter(f,dataArr[0].keys())
	w.writeheader()
	for i in range(len(dataArr)):
		w.writerow(dataArr[i])
	f.close()
	
	return None

def getJSON(tableName,parmType,start,end,proxy=False):
	'''
	build the url and extract the json object generated from it.
	'''
	
	url = "http://www.dbnfews.com/incoming/testing/sendToFEWS/getFromSQL.php?table=%s&type=%s&start=%s&end=%s"%(tableName,parmType,start,end)
	#print(url)
	#if proxy:
	#	PROXY = {'http': 'http://justin.pringle:!Backl1ne0117@proxy.durban.gov.za:80',
	#			'https': 'https://justin.pringle:!Backl1ne0117@proxy.durban.gov.za:80'}
	#	pProxy = urllib2.ProxyHandler(PROXY)
	#	auth = urllib2.HTTPBasicAuthHandler()
	#	opener = urllib2.build_opener(pProxy, auth, urllib2.HTTPHandler)
	#	urllib2.install_opener(opener)
	
	
	req = urllib2.Request(url,headers={'User-Agent' : "Magic Browser"})
	
	try:
		con = urllib2.urlopen(req)
		page = con.read()
	except urllib2.HTTPError:
		page = ''
	
	try:
		data = json.loads(page)
	except ValueError:
		data = []
	#print(page)
	
	return data	

def getTables(file):
	'''
	reads the input (in json format)
	returns a dictionary mapping table names to types
	'''
	tableDict={}
	with open(file) as f:
		lines = f.readlines()
		for l in lines:
			data = l.split(',')
			tableDict[data[0]]=data[1].strip('\n')
	
	return tableDict
	
if __name__=="__main__":
	
	
	t1 = dt.datetime.now()#.strftime("%s")
	t0 = t1 - dt.timedelta(days=1)
	
	start = t0.strftime("%s")
	end = t1.strftime("%s")
	
	check1 = dt.datetime.fromtimestamp(int(end)).strftime("%d/%m/%Y %H:%M:%S")
	check0 = dt.datetime.fromtimestamp(int(start)).strftime("%d/%m/%Y %H:%M:%S")
	
	
	fileIn = "input.dat"
	typeDict = getTables(fileIn)
	
	for k in typeDict:
		print(k)
		table = k	
		parmType = typeDict[table]
		
		dataDict = getJSON(table,parmType,start,end,proxy=False)
		print(len(dataDict))
		if len(dataDict)>0:
			saveFile("output_%s"%k,"",dataDict)
			
		else:
			with open("output_%s.csv"%k,'w') as f:
				f.write('')
			f.close()
			
			
		
		
				
	
	
	#print(start,check0,end,check1)
	
	
	#print(dataDict[0].keys())
	#
	#print(dataDict[0:5])
	#print(dataDict[0]["date"])
	
	
	
