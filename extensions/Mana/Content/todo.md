# Todo #

## Content Hierarchies ##

### New, Edit Delete ###

* show form with horizontal tabs:
    * initially for top page
    * then for any page specified in registry
    * add page breadcrumbs
    * add created/updated/deleted status
* when creating new hierarchy:
    * begin editing session
    * create new top "Unnamed Content" item in that session and register it
    * forward to edit page  
* when editing existing hierarchy:
    * begin editing session
    * load specified top item
    * check if it is really top item
* when clicking on tree item:
    * send AJAX with all pending edits
    * create/update records in database with current edits
    * receive AJAX with form for that tree item and with new pending edits data
    * replace form and save edit info
    * save current page id
* when clicking on "New Child Page" button:
    * send AJAX with new page and all pending edits
    * create/update records in database with current edits
    * create new page with specified parent
    * receive AJAX with form for that tree item and with new pending edits data
    * replace form and save edit info
    * save current page id
    * update tree
* when editing something:
    * save changes to edit info
    * if that's title, update tree
* when clicking on "Delete Current Page" button:
    * warn user about deletion, about child pages to be deleted, about deleting whole hierarchy (one of).
    * send AJAX with delete page reuest and all pending edits
    * create/update records in database with current edits
    * delete new page with specified id
    * receive AJAX with form for that tree item and with new pending edits data
    * replace form and save edit info
    * save current page id
    * update tree
* when expanding tree item marked to be ajaxified:
    * send AJAX request and receive 1 level down.
* Save, Apply, Close.
* Finish list of trees and lists (books and feeds)
    * Grid
    * Add dynamically to top submenu

### Store level ###



### Rename, Move, Redirects, Context Menu ###


## Content Lists ##




## Finishing It ##


#### Metadata ####

created_at: 2014-07-13 08:32