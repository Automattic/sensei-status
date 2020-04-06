### Sensei LMS - Status and Debugging Tools

Allows administrators to check the status of Sensei LMS, run various diagnostics, and provides several tools.

Snippet to enable the debug button in learner management:
```php
add_filter( 'sensei_show_enrolment_debug_button', '__return_true' );
```
