To import examples:

Windows :  
 Go to your MongoDB/bin directory, copy exportExamples to your C:\Temp directory and type:

>  mongoimport.exe -d xmongodb -c countries --file c:\Temp\exportExample.csv --type csv --headerline


Linux:
 Copy the file exportExamples.csv to /tmp and type:

mongoimport -d xmongodb -c countries --file /tmp/exportExample.csv --type csv --headerline



To launch examples:

