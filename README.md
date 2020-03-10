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
type | By default, the shortcode will produce a list of cities in UL LI format. If you want to use an inline format, just add _type=inline_ to your shortcode `[city_pages type=inline]`
after | only available if you have use inline as type. You can literally add any string after each city. `[city_pages type='inline' after='/']`
limit | basically, limits the output of the shortcode.
column | added in response to the location page, you can use 2 or 3. If you need to add extra column,  you have to add extra css with to met your requirements.


###Divi Global
Deprecated after Divi v4.0 has been released last October 17, 2019. The new divi builder replaces the **Divi Global** tab. Its main objected is to display global header and footer and now, it is deprecated since divi release its own version.


###Reports
_Coming Soon_


###Others
Available shortcode
[current_year] - display current year

_supported parameters_
start = if client wanted to add start year like 2015-2000, just add 2015
power_name = if this is a white label service, add power_name, it will replace **Wenotik Digital Agency**
power_url = required by power_name, this will link the power_name

[show_layout id=n] - display any layout from divi, just change n for to the layout ID.