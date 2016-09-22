
## Koala Framework Alternative Hreflang Integration

http://www.koala-framework.org/

open source framework for web applications and websites

#### Installation
Add to Root_Component:
```php
$ret['generators']['alternateHreflang'] = array(
    'class' => 'Kwf_Component_Generator_Box_Static',
    'component' => 'Kwc_AlternateHreflang_Component',
    'inherit' => true
);
$ret['editComponents'][] = 'alternateHreflang';
```
