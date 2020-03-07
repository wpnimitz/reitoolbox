##Welcome to REI Toolbox Help and Guidelines tab!

Here, you can view all available shortcode and the best way to use them throughtout divi theme.

###General Tab

In **General** tab, all shortcode is given. However, we have added few more shortcodes to enhance and speed up our process.

**[webnotik business=address]** - combines address line 1 and 2 in one complete address.

**[webnotik business=weburl]** - display the current address of the website.


###Branding

Branding tab is solely focus on the form branding. If the main setting under **Branding** tab is empty, it will use the defaults. 


###Forms
**Forms** tab is use to add our forms. 90% of the time, we use gravity forms with zapier integration. However, some client used CRM that doesn't allow zapier integration and thus we need to paste their forms here. Make sure that you stripped down all unnecessary html element here and make sure to check all fields. 

All forms are using the same styling, you may need to adjust some custom style it. All forms include 3 main classes:

-**gform_wrapper** - very basic parent class to setup our default form style.

-**webnotik-{form-type}** - to target specific form.

-**webnotik-form** - to target all forms.

_**Shortcodes** is also available and indicated with each form.


###City Pages
**City pages** tab brings the more power to our process. It includes cloning and renaming pages for easy-process to add more pages for our client business.

**[city_keywords]** - display the intended city keyword (city or estate) for that page.

**[city_pages]** - display a list of cities. Following your list in the **city pages** tab.
Check shortcode parameters.

Name | Description
---- | ----
type | by default, the shortcode will produce a list of cities in UL LI format. If you want to use an inline format, just add _type=inline_ to your shortcode `[city_pages type=inline]`
