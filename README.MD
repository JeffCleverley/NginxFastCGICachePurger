# NGINX FastCGI Cache Purger

Nginx FastCGI Purging is incredibly powerful, used appropriately static WordPress websites can handle enormous amounts of traffic and not choke.

That being said, fastCGI cache purging has always been a sticking point in it's implementation. Native Nginx FastCGI cache purging is one of the premium features of Nginx Plus, and as such users of the free platform have relied on third party modules to clear the cache when content updates.

The most commonly used module FRiCKLE's ngx_cache_purge module has been the go to recommendation of the community for some time, with Popular plugins such as [Nginx Helper](https://wordpress.org/plugins/nginx-helper/) and [Nginx Cache](https://wordpress.org/plugins/nginx-cache/) relying on this module to clear their cache.

For single user server stacks, where the Nginx Web Server user and the PHP web application user were one and the same, www-data or otherwise, this combination has been incredibly reliable and allowed many people to enjoy the awesome benefits of Nginx caching.

But not all server stacks are born equal, many platforms employ a more dynamic set up where each web application has it's own user, this allows for servers to host multiple applications that each have different resource requirements, which is absolutely fantastic... but makes clearing the cache a nightmare. When installed on a WordPress site hosted on a multi-user stack, neither of the plugins mentioned above have the filesystem permissions to do a complete cache purge, and so for a long long time we have been SSHing into our servers and purging the cache with a bash command.

This problem has gone unresolved for some time...

....well... not any more!

The Nginx FastCGI Cache Purger plugin, alongside a very specific Nginx compile and configuration, will allow your WordPress sites to clear their cache from within the WP-Admin.

The plugin is OOP but basic, it has been built as a foundation for others to take and do as they will. It has been provided to the community by a collaboration between GridPane and RunCloud, with me developing the plugin and working out how to implement it in WordPress based upon Jebat's identification of the correct module and call. Currently, the only two VPS management platforms that I am aware of that utilise this stack.

With GridPane, everything comes configured and works out of the box, but RunCloud will require some slight updates to your Nginx Configuration if you have been using their published tutorial.

For WordPress users, you can easily pull apart the plugin source code and see what's going on, but the basics of the compile and configuration should work with any PHP application, I have purposefully used PHP Curl commands in the plugin so that the basics can be implemented in Laravel, Drupal or otherwise.

So....

First things first...

This plugin does NOT do incremental purging. It is a proof of concept and as is should be used alongside Nginx Helper, think of it as an Nginx Helper Helper for Multi User Nginx Stacks.

It does one thing, and one thing only... Purges all Nginx FastCGI cache for WordPress site from within that site's WP-Admin.

## The Nginx Compile

Your Nginx compile needs to include the [Torden Fork of ngx_cache_purge module](https://github.com/torden/ngx_cache_purge), this will not work with the original FRiCKLE module.

The problem is, lots of pre built Nginx packages contain the ngx_cache_purge module, but how is one to know if yours is the right one?

In our case, we started from scratch and just made our own compile. An easy way to test is to use this plugin, and if it doesn't work, then you are using the 'other' ngx_cache_purge module.

Doing a recompile isn't too hard, there is a [tonne of good material online](https://serversforhackers.com/c/compiling-third-party-modules-into-nginx).

## The Nginx Configurations

I am not going to go into detail regarding all the Nginx Configurations required to properly tweak FastCGI caching

But it goes something like this:

````
http {

    ### Loads of other stuff above...
    
    fastcgi_cache_path /var/run/nginx-cache levels=1:2 keys_zone=WORDPRESS:100m inactive=60m;
    fastcgi_cache_key "$scheme$request_method$host$request_uri";
    fastcgi_cache_use_stale error timeout invalid_header http_500;
    fastcgi_ignore_headers Cache-Control Expires Set-Cookie;
   
    ### More stuff below....
   
    server {
       
        ### Loads of other stuff above... don't forget that
        
        set $skip_cache 0;
        
        # POST requests and urls with a query string should always go to PHP
        if ($request_method = POST) {
            set $skip_cache 1;
        }   
        if ($query_string != "") {
            set $skip_cache 1;
        }   
        
        # Don’t cache uris containing the following segments
        if ($request_uri ~* "/wp-admin/|/xmlrpc.php|wp-.*.php|/feed/|index.php|sitemap(_index)?.xml") {
            set $skip_cache 1;
        }   
        
        # Don’t use the cache for logged in users or recent commenters
        if ($http_cookie ~* "comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|wordpress_logged_in") {
            set $skip_cache 1;
        }
        
        #### AND THIS BELOW FOR PURGE ALL
        
        location ~ /purgeall {
            fastcgi_pass         php;
            fastcgi_cache        WORDPRESS;
            fastcgi_cache_purge  PURGE purge_all from 127.0.0.1;
        }
        
        #### And you'll need this for Nginx Helper purging on content update
        
        location ~ /purge(/.*) {
        	    fastcgi_cache_purge WORDPRESS "$scheme$request_method$host$1";
        }	
        
        location ~ \.php$ {
                
                #### Other stuff in your PHP block above
                   
                fastcgi_pass php;
                fastcgi_cache_bypass $skip_cache;
                fastcgi_no_cache $skip_cache;
                fastcgi_cache WORDPRESS;
                
                #### Maybe some other stuff below...
                
        }
    }
}
````

This block here is the key for purging all cache to work:

````
location ~ /purgeall {
        fastcgi_pass         php;
        fastcgi_cache        WORDPRESS;
        fastcgi_cache_purge  PURGE purge_all from 127.0.0.1;
}
````

## Installation

Like any other WordPress plugin...

## The Purging Mechanism

So what is the purging mechanism?

Well it's a not-so-simple curl call to the server from local host, ie the site.

````
curl -L -X PURGE -H "Host:an-example.com" "https://127.0.0.1/purgeall" -k
````

The problem was that on HTTPS sites this wouldn't work without another `if` block and redirect within the `server` context, and using WordPress HTTP API for these sorts of mutant curl calls was driving me insane, so I fell back to PHP and `CURLOPT_CUSTOMREQUEST`, ` CURLOPT_FOLLOWLOCATION`, `CURLOPT_RETURNTRANSFER`, and the secret sauce? ..... `CURLOPT_RESOLVE` for resolving the HTTPS calls.

## Contributions

So there you have it, feel free to use it, pull it apart and improve it. This has been a community problem and we would like it to remain a community solution, so we welcome any Pull Requests and contributions, it is our hope this plugin can be the foundation of something more functional in the long run that can better benefit everyone.

## The Self Managed WordPress Facebook Group

We have started a Facebook group for everyone who is rolling their own WordPress hosting solution using whatever tool of choice, all are welcome no matter if you use EasyEngine, ServerPilot, GridPane, RunCloud, or just pure terminal heroics. We invite anyone who reads this to join, if any of the above is pertinent to your needs then you are definitely one of us and we look forward to welcoming you.

[Join Us Here](https://www.facebook.com/groups/selfmanagedwordpress)












