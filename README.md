# SQL_Data_Management

This is where the magic of Data Management happens for the eThekwini FEWS project.
In this directory you will find scripts that connect to a "default" database structure, query it, populate it and retrieve data from it.

Explanation of this directory structure:

/SQL_Connections:
  - This contains a php class that talks to a relationship styled database.
  - This class is used to carry out the "dog work" of querrying the database.
  
/Incoming:
  - All php scripts that handle incoming data from consultants/gauges.
  
/Outgoing:
  - Send data from the SQL database to FEWS server.
  - Languages used: python, php.
