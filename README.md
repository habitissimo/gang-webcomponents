
#gang/webcomponent
  gang/webcomponents uses DomDocument to parse the document, for this reason [XML errors](http://www.xmlsoft.org/html/libxml-xmlerror.html) may appear.
  ###Require
#Quickstart

 ```php
 
    Configuration::$twig_cache_path = '/webcomponents/twig';
    Configuration::$library_cache_driver = new FilesystemCache('/webcomponents/library');
    Configuration::$library_base_namespace = "Web\Components";
    Configuration::$library_template_dir = "Web/Components";
    
    Configuration::$allow_create_error_file = true;
    Configuration::$error_file_path = "errors";
     
    $controller = new WebComponentController($logger);
    $controller->process($html);
  ```


#Configuration
We have a Configuration class with which you can configure the following parameters:

* **library_cache_driver:**
  we use **Doctrine** to cache the web components, for that reason if you want to cache your webcomponents you must add a **CacheProvider**, eg. FileSystemCache.
  By default is null.
  
* **library_cache_life_time:**
  you can add a maximum time to cache webcomponents. Default is 0 = never expires.
  
* **library_base_namespace:**
  add the namespace of the webcomponents
  
* **library_template_dir:**
  directory where the webcomponents are located
  
* **twig_cache_path:**
  we use twig to render the web components, to increase the performance you must add a path to cache them
 
* **allow_create_error_file :** 
  as at the moment of processing the content is manipulated, the line of error report may not be correct, 
  if you enable this option an html file will be created with the content that caused the error.
  
  Will create a file similar to this
  
  ```
  Error_2019-05-29_15:16:21.html
  ```
  
* **error_file_path:**
  write the path where the error html files will be added, by default they will be created in the root directory


# Errors 
* **Attribute redefined:** 
 appears when the tag have more than one attribute with the same name, when this error occurs
the first attribute will be chosen and the next attribute will be eliminated. 
  ```
  Input:
  <input class="first class" class="second class">
  
  Output:
  <input class="first class">
  ```

* **Unexpected end tag:** appears when malformed or unclosed tags are found


