# Vue 3 SFC for ConcreteCMS #

**NOTE: This project and its documentation are still in a proof-of-concept stage, and the package will likely be unstable while it is developed. Install and use this package at your own risk!**

This is the repository for the "abwd_vue3" ConcreteCMS package - a package which makes it easier to write Single File Components (SFCs) as blocks within ConcreteCMS.
Support for rendering out entire pages in Vue 3 ("Single Pages" within the CMS) is planned, either for larger reactive content or SPAs.

This project is neither endorsed nor supported by the ConcreteCMS/Vue core teams.

## Proposed Usage ##

### Making a reactive template for an existing ConcreteCMS Block ###
This project aims to make it easier to add reactivity to some core CMS blocks by creating a custom template that will take the same block information and pipe it as props into a Vue SFC. Obviously, this isn't necessary for many blocks, such as the pure content block, but may help in select cases where Vue could provide progressive enhancement (such as adding reactivity or removing the page navigations from the page list block).

### Making custom reactive blocks to include in the CMS ###
The primary goal of this project is to streamline the process for adding complex, reactive pieces of functionality to ConcreteCMS sites while not compromising the visual edit-in-place functionality, nor separating the view off of the monolithic architecture that ConcreteCMS provides.

### Entire Pages or SPAs ###
A future, more complex feature of this project will be the ability to host either ConcreteCMS's "Single Pages" or client-side SPAs within the CMS. An added benefit of architecting blocks as SFCs is the ability to import them directly into your single pages and just pass the props via data from an API response.

## License ##
This project is distributed under the Apache-2.0 License. A copy of the license text can be found in `LICENSE.txt` at the root of the repository.