api-samples-php
===============

PHP based code samples of integration with the ROI API

Overview:
This repository contains php files that allow a user to test API keys against Right-On Interactive's (ROI's) server to determine that they can be used to make requests to the server.

Installation:
In order to use this API tester, simply download this repository and place the three PHP files in the your localhost's directory, or where you will be able to access the php files from your web browser. It is important that your web browser has PHP support in order to use this program.

Directions:
Once the API Tester files have been appropriately located in your directory, open your web browser and locate index.php in your web directory. This is the home page for the program. Here, you will see two small forms that will allow you to enter your ROI credentials and make a call to the ROI server.

The first form is a simple test to the ROI server in order to validate whether the API key and secret key entered in the text boxes can be used to make requests to the ROI server. To use this, simply enter the API key and secret key given to you by ROI into the designated text fields and press Validate. The page will then display whether the credentials entered were able to be used to make a request to the server.

The second form is similar to the first form in that it makes a call to the ROI server in order to validate the credentials entered in the corresponding text fields. However, instead of simply indicating whether the web request was successful, this test makes a call to the server to display information about each entry in the Contacts Table of the database. If the call was made sucessfully, a table will be displayed showing the name, email, and phone number of every contact in the table, as well as the total number of results received from the request. If the call was not sucessful, an error message will be displayed indicating what went wrong.
