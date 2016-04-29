# AdwordsAPI
A small library to use google adwords api.

#### Installation and Usage

To install AdwordsAPI classes you need to copy this folder where you want it in your project. 
In this folder you will have a config.ini file. It is crucial for you to set your AdwordsAPI 
MCC account credentials here so that our classes can connect to the Google AdwordsAPI WebServices. 
So please set them carefully first otherwise you will get exceptions because if the credentials 
will not be good then google authentication checks will fail and as a result you will get error.

Following are the settings which you need to set for config.ini file.

email: This is the Google email account for you adwords reporting. This is required because 
Google needs to authenticate all requests that being made to its web services.

password: Password for the above email account. This is required to login.

sandbox: If you want to test APIs in sandbox then you first need to create a Google sandbox
account and then you need to set all the credentials to sandbox credentials and this setting
will accept either true or false. If you provide true that means you want to work in the 
Google sandbox environment for testing things and false means its a live environment.

clientCustomerId: This is the client id of the MCC account.

developerToken: assigned by google you will get if from your adwords MCC account.


https://adwords.google.com/support/aw/adwordsapi/bin/answer.py?hl=en&answer=15104

To know how to get a particular report then for that purpose we have created several sample reports.
This will just explain how to make a call and which method need to invoke.
