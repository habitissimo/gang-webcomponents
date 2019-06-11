
# gang/webcomponent
  gang/webcomponents uses DomDocument to parse the document, for this reason [XML errors](http://www.xmlsoft.org/html/libxml-xmlerror.html) may appear.
  
# How to create components
To create the custom components, you must create a directory with the name of the component in PascalCase,
inside this folder there must be at least two files,
* **The PHP class component:** This will be a flat class with all its attributes, its default values, setters and getters. 
It will also have an array with all of its required values ​​(called ``$required_fields``), in case it is necessary to create 
the HTML element. (A modal NEEDS to have a title)
* **Template twig:** It will be the HTML layout and you will receive all the attributes and children of the PHP class. 
The templates can include other components of our library including them as we will explain later. For more information on [TWIG templating](https://twig.symfony.com/doc/1.x/templates.html). 
It is recommended to have a directory that gathers all the components.

Let's see an example

Imagine we have this directory tree

```
Web
  │
  └───Components    
      │
      └───CustomButton
      │   └── CustomButton.php
      │   └── CustomButton.twig
      │   
      └───CoolInput
          └── CoolInput.php
          └── CoolInput.twig
```
Let's see the ``CoolInput`` component
```php
 <?php
 namespace Web\Components\CoolInput;
 
 use Gang\WebComponents\HTMLComponent;
 
 class CoolInput extends HTMLComponent
 {
     protected $required_fields = ['be_cool'];
     
     public $be_cool;
 
     public function setBeCool($be_cool)
     {
         $this->be_cool= $be_cool;
     }
 }
```

As you can see the class ``CoolInput`` extends from ``HTMLComponent``, all the webcomponents should extend this class so that 
they are recognized as such.

Now let's see the template twig
```html
<input class ="{% if be_cool %} im-cool {% else %} ">
        {{ children | raw }}
</input>
```
## Warning!
The elements must always be wrapped in a tag. if they are not wrapped in a tag it may not show the expected result or 
cause an error.
```html
<!--Wrong-->
  <input>Im input</input>
  <button>Im button</button>
  
<!--Correct-->
  <div>
    <input>Im input</input>
    <button>Im button</button>
  </div>
```
## Tip

To add the class attribute you must write the classname, remember that we use DomDocument, therefore, everything will 
be converted to lowercase.

# How to use webcomponents
We already have the webcomponents created, it's time to use them.

To use them we must add the prefix ``wc-`` plus the name of the component in kebab case
```html
<wc-cool-input be-cool="true">I'm the coolest</wc-cool-input>
```
# WebComponentController
The WebComponentController class is the one that provides the html and will replace the webcomponents with the twig template of that component.

Before the instance, we must do some previous steps, we must add the configuration, below it explains the options in detail.

The class accepts two parameters, a logger to record failures in the application and the ComponentLibrary class, which, 
if you have done the configuration, it will not be necessary to use it.

If you don't want to use the logger, do not add it, we will use a NullLogger so there is no problem

Let's see a quick example

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
# Diving deeper into the functionality

As a point of entry to the render of web components, we have the mentioned class WebComponentController.

To render the content, we must call the process function, passing it as parameter the html text. 
This function before creating the dom tree will replace the ``script`` and ``noscript`` tags since they cause problems with DomDocument. 
This will simply replace the content of the tags with a tag similar to this ``<replace-script id =0></ replace-script>``.
In the Dom class that we provide you will have a array to which you can add more confictive tags.

Once this previous step is done we will create the DOM tree by calling ``Dom::domFromString($html, $logger)`` that accepts the text and 
the logger, this will return us a DomDocument object, to obtain the webcomponents parents, those who do not have any 
webcomponent parents, we will call the function 
``$this->getParentWebComponents();``, it will return an array of DomElements that will be the webcomponets to render.

Once the array is obtained, we will enter a while loop where we will not finish rendering until there isn't webcomponents.
Inside the loop we will create an HTML component through DomElement. For this we must call the class ``HTMLComponentFactory``
which is responsable for the creation of our HTMLComponents. The ``HTMLComponent`` class has a method that is responsible for
rendering it self since it has total control of how their children are rendered.

From our loop we will call ``$htmlComponent->render($this->renderer, $this->dom, $this-> factory);`` 
with all the elements he needs to render.

* **Render:** 
class that is responsible for transforming the HTMLComponent to the template twig with it's values.
* **DomDocument:**
it's used if it's necessary to obtain the html of their children, usually children that are not webcomponent.
* **HTMLFactory:** 
if one of their children is a webcomponent then it will create a HTMLComponent, that's why the HTMLComponentFacttory 
class is necessary

To explain how the render method works, better if we look at the code.

```php
public function render($renderer, $dom , $factory)
  {
    foreach ($this->DOMElement->childNodes as $child) {
      if (Dom::isWebComponent($child)) {
        $HTMLComponentChild = $factory->create($child);
        $this->innerHtml .= $HTMLComponentChild->render($renderer, $dom, $factory);
      } else {
        $this->innerHtml .= html_entity_decode ($dom->saveHTML($child));
      }
    }

    return $renderer->render($this);
  }
```

Let's explain a little in detail. The HTMLComponent class has as an attribute the DomElement that is the same in 
the DomDocument. So, we are going through their children and if one of them is a webcomponent we proceed to create an 
HTMLComponent and we will tell the child to renderize, so through the father we can render all of their children with 
only one iteration.

Once we have gone through all the children, we render to the father and return him.

We return to be in the WebComponentController, once obtained the HTMLComponetn rendering we will replace the old DomElement 
with the new rendering, for that, we will call replaceChildNodeToWebComponetRendered of the class Renderer.
We will ask again if there are any WebCoponents in the DomElement, since the templates may have generated other WebCoponents.

Finish the loop while we will call the DomDocument to return the rendered HTML and re-add the previously replaced scripts 
and noscripts.

# Configuration
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


