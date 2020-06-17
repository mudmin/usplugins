<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.
$hooks['user_settings.php']['bottom'] = 'hooks/settingsbottom.php';
registerHooks($hooks,$plugin_name);
$check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($check > 0){
	err($plugin_name.' has already been installed!');
}else{
 $db->query("ALTER TABLE settings ADD COLUMN gdpract tinyint(1) DEFAULT 0");
 $db->query("ALTER TABLE settings ADD COLUMN gdprver int(11)");
 $db->query("ALTER TABLE users ADD COLUMN gdpr int(11) DEFAULT 0");
 $db->query("ALTER TABLE users ADD COLUMN gdpr_date DATETIME DEFAULT NULL");
 $db->query("CREATE TABLE `us_gdpr` (
	 `id` int(11) NOT NULL,
	 `popup` text,
	 `detail` text,
	 `confirm` text,
	 `btn_accept` varchar(255),
	 `btn_more` varchar(255),
	 `btn_delete` varchar(255),
	 `btn_confirm_no` varchar(255),
	 `btn_confirm_yes` varchar(255),
	 `delete` tinyint(1) DEFAULT 0,
	 `created_on` DATETIME
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
 $db->query("ALTER TABLE `us_gdpr`
	 ADD PRIMARY KEY (`id`)");
	 $db->query("ALTER TABLE `us_gdpr`
		 MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
$check = $db->query("SELECT id FROM us_gdpr")->count();
if($check < 1){
 $fields = array(
	 'popup'=>"We use cookies and collect personal information in accordance with our policy to provide you with the best possible user experience.",
	 'detail'=>"",
	 'confirm'=>"Are you absolutely SURE you want to delete your account. This cannot be undone!",
	 'btn_accept'=>"Accept",
	 'btn_more'=>"More Info",
	 'btn_delete'=>"Delete My Account",
	 'btn_confirm_no'=>"No! I Changed My Mind.",
	 'btn_confirm_yes'=>"Yes, Please Delete My Account",
	 'delete'=>"0",
	 'created_on'=>date("Y-m-d H:i:s"),
 );
 $db->insert('us_gdpr',$fields);
 $id = $db->lastId();
 $db->update('us_gdpr',$id,[
	 'detail'=>"1. Introduction&lt;br&gt;
This information sheet serves to inform you about our website&rsquo;s (&ldquo;Website&rdquo;) Cookie Policy so that you may better understand the use of cookies during your navigation and provide your consent thereto.
&lt;br&gt;&lt;br&gt;
It is understood that by continuing to navigate on this Website you are consenting to the use of cookies, as specifically indicated in the notice on the front page (&ldquo;Homepage&rdquo;) reporting the existence of our Cookie Policy.
&lt;br&gt;&lt;br&gt;
2. Who is the controller of your data?&lt;br&gt;
When this policy mentions &ldquo;Company&rdquo;, &ldquo;we,&rdquo; &ldquo;us,&rdquo; &ldquo;our&rdquo; or &ldquo;Data Controller&rdquo;, it refers to the website you are visiting right now.
&lt;br&gt;&lt;br&gt;
3. What are cookies?&lt;br&gt;
Cookies are small files which are stored on your computer, they hold a modest amount of data specific to you and allows a server to deliver a page tailored to you on your computer, hard drive, smartphone or tablet (hereinafter referred to as, &ldquo;Device&rdquo;). Later on, if you return to our Website, it can read and recognize the cookies. Primarily, they are used to operate or improve the way our Website works as well as to provide business and marketing information to the Website owner.
&lt;br&gt;&lt;br&gt;
4. Authorization for the use of cookies on our Website&lt;br&gt;
In accordance with the notice of cookie usage appearing on our Website&rsquo;s homepage and our Cookie Policy you agree that, when browsing our Website, you consent to the use of cookies described herein, except to the extent that you have modified your browser settings to disable their use. This includes but is not limited to browsing our Website to complete any of the following actions: closing the cookie notice on the Homepage, scrolling through the Website, clicking on any element of the Website, etc.
&lt;br&gt;&lt;br&gt;
5. What categories of your data do we collect and use?&lt;br&gt;
When you visit the Website (you as a &quot;User&quot;) we collect the categories of personal data as follows:
&lt;br&gt;&lt;br&gt;
Personal data collected  during your signup and automatically from our Website.
&lt;br&gt;&lt;br&gt;
Information about your visits to and use of the Website, such as information about the device and browser you are using, your IP address or domain names of the computers connected to the Websites, uniform resource identifiers for requests made, the time of request, the method used to submit the request to the server, the size of the archive obtained as a response, the numerical code indicating the status of the response given by the server (correct, error, etc.) and other parameters relative to the operating system and the computer environment used, the date and time that you visited, the duration of your visit, the referral source and website navigation paths of your visit and your interactions on the Website including the Services and offers you are interested in. Please note that we may associate this information with your account. If you delete your account, this information will no longer be linked to you in our database.
&lt;br&gt;&lt;br&gt;
Please see the following clause of this Policy for further information on the purposes for which we collect and use this information.
&lt;br&gt;&lt;br&gt;
6. Types of cookies used on our Website&lt;br&gt;&lt;br&gt;
6.1. Types of cookies according to the managing entity&lt;br&gt;

Depending on what entity manages the computer or domain from which the cookies are sent and processed, there exist the following types of cookies:
&lt;br&gt;
First party cookies: these are sent to your Device from a computer or domain managed by us and from which the service you requested is provided.&lt;br&gt;&lt;br&gt;
Third party cookies: these are sent to your Device from a computer or domain that is not managed by us, but by a separate entity that processes data obtained through cookies.&lt;br&gt;&lt;br&gt;
6.2. Types of cookies according to the length of time you stay connected: &lt;br&gt;

Depending on the amount of time you remain active on your Device, these are the following types of cookies:
&lt;br&gt;&lt;br&gt;
Session cookies: these are designed to receive and store data while you access the Website. These cookies do not remain stored on your Device when you exit the session or browser.&lt;br&gt;&lt;br&gt;
Persistent cookies: these types of cookies remain stored on your Device and can be accessed and processed after you exit the Website as well as when you navigate on it for a pre-determined period of time. The cookie remains on the hard drive until it reaches its expiration date. The maximum time we use persistent cookies on our Website is 2 years. At this point the browser would purge the cookie from the hard drive.&lt;br&gt;&lt;br&gt;
6.3. Types of cookies according to their purpose&lt;br&gt;

Cookies can be grouped as follows:
&lt;br&gt;&lt;br&gt;
Technical cookies: these cookies are strictly necessary for the operation of our Website and are essential for browsing and allow the use of various features. Without them, you cannot use the search function, compare tool or book other available services on our Website.&lt;br&gt;&lt;br&gt;
Personalization cookies: these are used to make navigating our Website easier, as well as to remember your selections and offer more personalized services. In some cases, we may allow advertisers or other third parties to place cookies on our Website to provide personalized content and services. In any case, your use of our Website serves as your acceptance of the use of this type of cookie. If cookies are blocked, we cannot guarantee the functioning of such services.&lt;br&gt;&lt;br&gt;
Analytical cookies for statistical purposes and measuring traffic: these cookies gather information about your use of our Website, the pages you visit and any errors that may occur during navigation. We also use these cookies to recognize the place of origin for visits to our Website. These cookies do not gather information that may personally identify you. All information is collected in an anonymous manner and is used to improve the functioning of our Website through statistical information. Therefore, these cookies do not contain personal data. In some cases, some of these cookies are managed on our behalf by third parties, but may not be used by them for purposes other than those mentioned above.&lt;br&gt;&lt;br&gt;
Advertising and re-marketing cookies: these cookies are used to gather information so that ads are more interesting to you, as well as to display other advertising campaigns along with advertisements on the Website or on those of third parties. Most of these cookies are &ldquo;third party cookies&rdquo; which are not managed by us and, because of the way they work, cannot be accessed by us, nor are we responsible for their management or purpose.
&lt;br&gt;&lt;br&gt;
To that end, we can also use the services of a third party in order to collect data and/or publish ads when you visit our Website. These companies often use anonymous and aggregated information (not including, for example, your name, address, email address or telephone number) regarding visits to this Website and others in order to publish ads about goods and services of interest to you.&lt;br&gt;&lt;br&gt;
Social cookies: these cookies allow you to share our Website and click &ldquo;Like&rdquo; on social networks like Facebook, Twitter, Google, and YouTube, etc. They also allow you interact with each distinct platform&rsquo;s contents. The way these cookies are used and the information gathered is governed by the privacy policy of each social platform, which you can find on the list below in Paragraph 5 of this Policy.&lt;br&gt;&lt;br&gt;

7. List of cookies used on this Website&lt;br&gt;
Default Browser/Login Cookie&lt;br&gt;

We are not responsible for the contents and accuracy of third party cookie policies contained in our Cookie Policy.
&lt;br&gt;&lt;br&gt;
8. Why do we collect your data?&lt;br&gt;
A. To create and maintain the contractual relation established for the provision of the Service requested by you in all its phases and by way of any possible integration and modification.&lt;br&gt;
To provide a requested service&lt;br&gt;&lt;br&gt;
B. To meet the legal, regulatory and compliance requirements and to respond to requests by government or law enforcement authorities conducting an investigation.&lt;br&gt;&lt;br&gt;
To comply with the law&lt;br&gt;
C. To carry out anonymous, aggregation and statistical analyses so that we can see how our Website, products and services are being used and how our Website is performing.&lt;br&gt;&lt;br&gt;
To pursue our legitimate interest(i.e. improving our Website, its features and our products and services)&lt;br&gt;&lt;br&gt;

D. To tailor and personalize online marketing notifications and advertising for you based on the information on your use of our Website, products and services and other sites collected through cookies.&lt;br&gt;
Where you give your consent (i.e. through the cookie banner or by your browser's settings)&lt;br&gt;&lt;br&gt;
9. How long do we retain your data?&lt;br&gt;
We retain your personal data for as long as is required to achieve the purposes and fulfill the activities as set out in this Cookies Policy, otherwise communicated to you or for as long as is permitted by applicable law. Further information about the retention period is available here:
&lt;br&gt;&lt;br&gt;
Data collected-retention period&lt;br&gt;
Technical cookies-Max 3 years from the date of browsing on our websites&lt;br&gt;
Non-technical cookies-Max 1 year	from the date of browsing on our websites&lt;br&gt;
&lt;br&gt;&lt;br&gt;
10. Cookie management&lt;br&gt;
You must keep in mind that if your Device does not have cookies enabled, your experience on the Website may be limited, thereby impeding the navigation and use of our services.&lt;br&gt;

10.1.- How do I disable/enable cookies?&lt;br&gt;

There are a number of ways to manage cookies. By modifying your browser settings, you can opt to disable cookies or receive a notification before accepting them. You can also erase all cookies installed in your browser&rsquo;s cookie folder. Keep in mind that each browser has a different procedure for managing and configuring cookies. Here&rsquo;s how you manage cookies in the various major browsers:&lt;br&gt;&lt;br&gt;

Here&rsquo;s how you manage cookies in the various major browsers:&lt;/p&gt;&lt;ul&gt;&lt;li&gt;&lt;a href=&quot;https://support.microsoft.com/en-us/kb/278835&quot;&gt;MICROSOFT INTERNET EXPLORER/EDGE&lt;/a&gt;&lt;/li&gt;&lt;li&gt;&lt;a href=&quot;https://support.google.com/chrome/answer/95647?hl=en-GB&quot;&gt;GOOGLE CHROME&lt;/a&gt;&lt;/li&gt;&lt;li&gt;&lt;a href=&quot;https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences?redirectlocale=en-US&amp;amp;redirectslug=Enabling+and+disabling+cookies&quot;&gt;MOZILLA FIREFOX&lt;/a&gt;&lt;/li&gt;&lt;li&gt;&lt;a href=&quot;http://www.apple.com/support/?path=Safari/5.0/en/9277.html&quot;&gt;APPLE SAFARI&lt;/a&gt;&lt;/li&gt;&lt;/ul&gt;&lt;p&gt;
&lt;br&gt;
If you use another browser, please read its help menu for more information.
&lt;br&gt;
If you would like information about managing cookies on your tablet or smartphone, please read the related documentation or help archives online.&lt;br&gt;&lt;br&gt;

10.2.- How are third party cookies enabled/disabled?&lt;br&gt;

We do not install third party cookies. They are installed by our partners or other third parties when you visit our Website. Therefore, we suggest that you consult our partners&rsquo; Websites for more information on managing any third party cookies that are installed. However, we invite you to visit the following website http://www.youronlinechoices.com/ where you can find useful information about the use of cookies as well as the measures you can take to protect your privacy on the internet.
&lt;br&gt;&lt;br&gt;
11. What are your data protection rights and how can you exercise them?&lt;br&gt;
You can exercise the rights provided by the Regulation EU 2016/679 (Articles 15-22), including the right to:&lt;br&gt;

Right of access - To receive confirmation of the existence of your personal data, access its content and obtain a copy.&lt;br&gt;&lt;br&gt;
Right of rectification - To update, rectify and/or correct your personal data.&lt;br&gt;&lt;br&gt;
Right to erasure/right to be forgotten and right to restriction - To request the erasure of your data or restriction of your data which has been processed in violation of the law, including whose storage is not necessary in relation to the purposes for which the data was collected or otherwise processed; where we have made your personal data public, you have also the right to request the erasure of your personal data and to take reasonable steps, including technical measures, to inform other data controllers which are processing the personal data that you have requested the erasure by such controllers of any links to, or copy or replication of, those personal data.&lt;br&gt;&lt;br&gt;
Right to data portability - To receive a copy of your personal data you provided to us for a contract or with your consent in a structured, commonly used and machine-readable format (e.g. data relating to your purchases) and to ask us to transfer that personal data to another data controller.&lt;br&gt;&lt;br&gt;
Right to withdraw your consent - Wherever we rely on your consent, you will always be able to withdraw that consent, although we may have other legal grounds for processing your data for other purposes.&lt;br&gt;&lt;br&gt;
Right to object, at any time
You have the right to object at any time to the processing of your personal data in some circumstances (in particular, where we don&rsquo;t have to process the data to meet a contractual or other legal requirement, or where we are using your data for direct marketing.&lt;br&gt;&lt;br&gt;
You can exercise the above rights at any time by:&lt;br&gt;&lt;br&gt;"
]);
} //end duplicate data check



 $fields = array(
	 'plugin'=>$plugin_name,
	 'status'=>'installed',
 );
 $db->insert('us_plugins',$fields);
 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins",$plugin_name." installed");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}



} //do not perform actions outside of this statement
