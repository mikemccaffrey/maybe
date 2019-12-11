# Maybe module for Drupal 8

This module lets you access drupal entities without worrying about accidentally triggering a fatal exception.

The class is loosely based on the Maybe Monad, but customized for the particularities of PHP/Drupal and tailored specifically for accessing and traversing nested entities.

The module helps protect you from four common issues that would usually trigger a fatal exception:

* Calling a method on null when the previous method did not return an expected object.
* Calling a method that doesn't exist on the current object.
* Trying to access the first element of an unexpectedly empty array.
* Trying to access an entity field that does not exist in the current object.

In any of these cases, the Maybe object returns null, so it can be used whenever the lack of a result won't cause problems.

## Usage

Instead of calling object methods on a drupal entity, you instead call them on a new Maybe object, and then get the result at the end:
```
use Drupal\maybe\Maybe;
...
$output = (new Maybe($entity))->function1()->function2()->return();
```

To help make the syntax a bit more comprehensible, the module provides a simple maybe() function that can access the namespace and create the object for you:
```
$output = maybe($entity)->function1()->function2()->return();
```

Here is an example from a theme preprocess function for getting the download link of a file entity referenced by a media entity referenced by a paragraph entity:
```
$variables['file_url'] = maybe($paragraph)->get('field_media_file')->referencedEntities()->get('field_media_file')->referencedEntities()->url()->return();
```

That is the equivalent of this error-prone code:
```
$variables['file_url'] = $paragraph->get('field_media_file')->referencedEntities()[0]->get('field_media_file')->referencedEntities()[0]->url();
```

Without using Maybe, you would need to write 21 lines of code with 7 IF statements to traverse those references while protecting against exceptions:
```
// Make sure we have a file_download paragraph with a field_media_file field.
if ($paragraph->getType() == 'file_download') {

  // Get the value of the field that references the media item.
  $media_reference = $paragraph->get('field_media_file');
  if ($media_reference) {
  
    // Get the id for the referenced media identity.
    $media_id = $media_reference->getString();
    if ($media_id) {
    
      // Load the referenced media entity.
      $media_entity = \Drupal\media\Entity\Media::load($media_id);
      if ($media_entity) {
      
        // Get the value of the field that references the file.
        $file_reference = $media_entity->get('field_media_file');
        if($file_reference) {
        
          // Get the id for the referenced file.
          $file_id = $file_reference->getString();
          if ($file_id) {
          
            // Get the referenced file entity.
            $file_entity = \Drupal\file\Entity\File::load($file_id);
            if ($file_entity) {
            
              // Save the url for the file for use by the twig template.
              $variables['file_url'] = $file_entity->url();
            }
          }
        }
      }
    }
  }
}
```

## Future development

Planned features:
- access an arbitrary array item
- call methods on all items in an array
- return the propery of an object
- alteratives to the ->return() function that specify the intended result type, such as ->string or ->array.

Please feel free to submit an issue if you find a bug or would like to request a feature: https://github.com/mikemccaffrey/maybe
