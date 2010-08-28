#put  this in your pg_hba.conf
## "local" is for Unix domain socket connections only
#local   all         all                               trust


#as postgres superuser
createuser budget
createdb  -U budget budget

