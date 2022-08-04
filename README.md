# Scrap Enquiry - A plugin for WordPress

This plugin is custom built to retrofit an integration into a specific existing web site.

The site has a form that is used to send an enquiry to receive a quote for selling precious metals to the store operator. The code behind the form sends email to the store operator, who assesses the items and sends an offer back to the customer via email.

The goal of this plugin is to intercept the form submission, create a custom post in the WordPress admin area, allow the store owner to edit the content of the enquiry request, and send email when appropriate to either the store operator or the customer based on the changeable post status.

This plugin creates a custom post to facilitate that effort. It uses a custom post type and custom post statuses, a Javascript driven post content editor, custom endpoints in WordPress for email click handling to accept or reject the quote provided to the customer, email messages integrated into WooCommerce, a custom endpoint and custom template for the My Account area in WooCommerce, shortcodes, and hooks that interact with Advanced Custon Fields to update the quoted prices.

All of the functionality is custom designed to fit the exact needs of the site operator while avoiding any modifications to the site's existing theme functionality.