<html>
 <head>
  <title>API Authentication</title>
 </head>
 <body>
 <h3>Enter information and click submit to validate credentials</h3>
 <div style= "width: 50%; border-width: 1px; border-color: #E6E6E6; border-style: solid; border-radius: 7px; padding: 5px; margin-left: 5px; margin-bottom: 10px;">
 <p><u>Sample Test 1:</u><br>
This is a sample test that allows you to test whether your API credentials work properly or not. Simply enter in your API and secret key into the text boxes below and press validate.</p>
</div>
 <form action= "test.php" method= "post" id= "testform">
 <div style= "background: #E6E6E6; width: 30%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
 <label><u>Credentials:</u></label><br>
 API Key: <input type= "text" name= "api"><br>
 Secret Key: <input type= "text" name= "secret"><br>
</div>
 <button type= "submit">Validate</button>
 </form>
 <hr size = "1"  />
 <h3>Display contacts table</h3>
 <div style= "width: 50%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
 <p><u>Sample Test 2:</u><br>
 This is a second sample test that will allow you to view the contents of your contacts table as well as check to see if your API key and Secret key are valid. Simply enter in your API key and secret key into the blanks below and press Show Table. If your credentials are approved, you will be able to view information from your Contacts table. If an error occurred, your API key or secret key didn't were not approved.</p>
 </div>
 <form action= "Display.php" method= "post">
 <div style= "background: #E6E6E6; width: 25%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
 <label><u>Credentials:</u></label><br>
 API Key: <input type= "text" name= "api"><br>
 Secret Key: <input type= "text" name= "secret"><br></div>
 <button type= "submit">Show Table</button>
 </form>
 </body>
</html>