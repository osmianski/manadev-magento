# Infinite Scrolling #

There are several moving parts:

* **Scrolling**. Observes user scrolling and informs *Paging* to possibly change current page number.
    * `onScroll`
* **Paging**. Calculates current page number based on scroll position.
    * `getPageAtScrollingBottom`
* **Loader**. Loads additional pages.
    * `getContent`
* **History**. Manages history API.
    * `getURLPage`
    * `setURLPage` 

#### Metadata ####

created_at: 2014-05-27 23:48