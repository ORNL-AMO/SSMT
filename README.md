# Steam System Modeler Tool (SSMT)
### or steamtool

Developed for the US Department of Energy's Advanced Manufacturing Office.

The Steam System Modeler Tool (SSMT) provides a variety of steam related 
calculations and models with comprehensive calculation descriptions and details.

## Installation Instructions

###Step 1:
    Place "steamtool" folder where ever you like outside of the public html folder.

###Step 2:
    Place the contents of the public folder in the public html folder 
    associated with the url location.
         
    Not knowing the specifics of your server configuration 
    here is a Simple Server Example:
              something like url = http://www.doe.gov/eere/amo/steamtool/
              folder would be
    /var/www/html/eere/amo/steamtool/"place public folder contents here"

###Step 3:
    Modify the "index.php" file that you just moved from the public folder so that
    the 'STEAMTOOL_APPLICATION_FOLDER' constant points to the location of the directory
    you placed the "steamtool" folder.   
         >>Specifically modify line:   
         define('STEAMTOOL_APPLICATION_FOLDER', '/var/www/html/steamtool/');


## Developed By 

Michael B Muller 
<michael.b.muller@analyticalenergy.com> 
