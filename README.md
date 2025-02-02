# 📚 Zoom Into Books

Zoom Into Books is a [University of Cambridge CST Group Project](https://www.cst.cam.ac.uk/teaching/part-ib/group-projects). Our project won the **2021 Most Professional Project prize** out of 21 projects.

It is a fully featured app and web interface for augmenting books with online content such as high-resolution images, links and videos. The app lets you scan books and will auto-detect pages and images in the viewfinder, automatically delivering augmented content to the user (without the need for them to type in links or scan QR codes). The web interface allows a publisher to upload books and set triggers and content for delivery.

**See the Youtube video for an explanation and demo:**

[![Click for youtube video](http://img.youtube.com/vi/aE2AjQbZ1cI/0.jpg)](https://www.youtube.com/watch?v=aE2AjQbZ1cI "Zoom Into Books | Video Presentation")

The original project brief:
> Art and travel books often have beautiful images, but it’s frustrating that you can’t pinch to zoom as you would with a phone, to see arbitrarily high resolution details. The purpose of this project is to identify those times when a picture in a book or magazine corresponds to an existing high resolution image that is available online. Your Android app should work in augmented reality style, starting with a view of the book through the phone camera, but then seamlessly zooming by substituting high-resolution online data.

## Code tour

- `api`: Contains our back-end API code, written in Flask & Python
- `html`: Contains our back-end web console code, written in PHP & HTML5
- `app`: Contains the code of our Android client, written in Java (with some GLSL)
- `ocr`: Contains the server-side code for OCR scanning, written in Python.

## Android App Tour

#### `\app\src\main\java\com\uniform\zoomintobooks`

- AugmentedImageActivity.java

The main activity that performs AR and OCR image recognition.

- BarcodeScanActivity.java

The activity that scans a barcode and returns its numerical decoding.

- ContactActivity.java

Empty activity to provide a way to contact us or the publisher in the future

- ImageActivity.java

The activity which displays a gesture controlled image.

- InfoActivity.java

Empty activity to provide information about us in the future

- ListViewAdapter.java

An adapter to help SelectBookActivity manage the list of books

- ResourceHandlerActivity.java

The activity which handles the displaying, opening and storage of non ar resources.

- SelectBookActivity.java

This activity allows the user to select a book from the database by searching for it by title.

- SettingsActivity.java

Allows the user to switch between Light and Dark Mode. Provides framework for 2 other settings, should they be implemented in the future.

- WebViewActivity.java

Allows for opening arbitrary linked resources by URL, and displaying them to the user.

- WelcomeActivity.java

Beginning activity where the user chooses how they want to select the book.


**See also** `\app\src\main\java\com\uniform\zoomintobooks\common\helpers`, which contains many helper classes used within the activities above. The main classes of interest are:

- BookInfo.java and BookResource.java

These define the structure of both a book and an individual resource, once JSON objects returned by the API have been decomposed.

- AugmentedImageState.java

For a given detected image, this class stores the renderer for the overlay image, the bitmap to be rendered and the augmented image itself.

- ZoomUtils.java

Utility functions that parse JSON output from the API, decode the base64 encoded string that represents the database of images to detect (for use in AugmentedImageActivity.java), and generate an input stream given an image URL.

## Web tour

There are two account types: standard and administrator. Standard users can interact with the books and resources which they have created themselves, and administrators can interact with all books and resources as well as creating and editing users and publishers. Each standard user is associated with a publisher (administrators have no publisher) and all books which they create are associated with that publisher.

Most form interaction between the client and server is done asynchronously using JavaScript's XMLHTTPRequest, for sending, not XML, but POST query data to the server and receiving JSON back, indicating the status of the operation.

On the server, all operations must be authorised by a central function before they can proceed, and all operations are carried out as transactions so that the system remains consistent. MySQL provides transactions but for filesystem operations a custom system is used.

### Pages (`/html/`)

Many of the `index.php` pages take GET parameters, such as `isbn` for books or `rid` for resources. The `action.php` pages and other form handlers all require POST requests.

- `/login/`: `index.php` is the login form and `action.php` is the login form handler
- `/logout/`: `index.php` is the logout handler which automatically redirects to the homepage when run
- `/console/books/`: `index.php` shows the list of books which the user is permitted to view and edit (for administrators this is all books)
- `/console/books/book/`: `index.php` is the book editing page, `action.php` performs updates to a book's properties, `delete.php` deletes a book and `unlink.php` unlinks a resource from a book
- `/console/books/book/cover/`: `index.php` shows the cover of a book as image/png
- `/console/books/book/image/`: `index.php` shows a specific trigger image from a book
- `/console/books/book/resource/new/`: `index.php` is the book-resource linking page and `action.php` performs the linking and AR/OCR blob generation
- `/console/books/book/upload`/: `index.php` shows the PDF copy of a book
- `/console/books/new/`: `index.php` is the book creation form page and `action.php` performs the actual book creation in the database and on the filesystem
- `/console/publishers/`: `index.php` shows the list of all publishers (only accessible to administrators)
- `/console/publishers/new/`: `index.php` is the publisher creation form and `action.php` performs the creation
- `/console/publishers/publisher/`: `index.php` is the publisher editing page, `action.php` performs the updates and `delete.php` performs deletions
- `/console/resources/`: `index.php` shows a list of all resources that the user can view and edit (for administrators this is all of them)
- `/console/resources/new/`: `index.php` is the resource creation page and `action.php` actually creates the resource
- `/console/resources/resource/`: `index.php` is the resource editing page, `action.php` performs the updates and `delete.php` performs deletions
- `/console/resources/resource/preview/`: `index.php` shows a preview of the resource as image/png
- `/console/resources/resource/upload/`: `index.php` shows the resource (if it is hosted on our server)
- `/console/users/`: `index.php` shows the list of all users (only accessible to administrators)
- `/console/users/new/`: `index.php` is the user creation form and `action.php` performs the creation
- `/console/users/user/`: `index.php` is the user editing page and `action.php` performs the updates and `delete.php` performs deletions


### PHP 'modules' (`/html/assets/modules/`)

#### `books.php`, `resources.php`, `users.php`, `publishers.php`
These modules contains all operations on the entities after which they are named. The functions in each one tend to be pretty similarly named, hence why they're grouped together here. In the function list below, `*` stands for one of the entity types.

##### Repeated functions

- `show_*_form` shows the entity addition/editing form, selected using the `$edit` argument
- `manage_*_publisher` handles entity addition and editing, again selected using the `$edit` argument
- `delete_*` handles entity deletion
- `can_edit_*` determines whether the current user is allowed to edit the entity
- `*_exists` determines whether the entity in question exists in the database
- `fetch_*` fetches a single entity, specified by its id (e.g. ISBN, username, etc.)
- `fetch_*s` fetches a list of all of those entities which are available to the user (e.g. books which the user is allowed to view)

##### Specific functions
`books.php`:
- `count_resources` returns the number of resources linked to this book
- `get_book_type` returns the MIME type of the uploaded PDF and `NULL` if no PDF was uploaded
- `update_blobs` calls `generate_ar_blob` and `generate_ocr_blob`. This is called whenever an operation updates the links between a book and its resources
- `generate_image_list` generates a text file in the format required by `arcoreimg` for reading in the input image triggers
- `generate_ar_blob` invokes Google's `arcoreimg` on the image triggers specified to create a blob used by the app to detect image-based triggers. It calls `generate_image_list` to create the input list for `arcoreimg`
- `generate_ocr_blob` invokes `/ocr/extract_pdf.py` on the PDF uploaded with the book to create a JSON object which the app can use to detect text-based triggers

`resources.php`:
- `fetch_book_resources` returns a list of resources associated with the given book
- `get_resource_mime_type` returns the MIME type of the resource if we host it, and `NULL` otherwise
- `was_resource_uploaded` returns `true` if the resource is hosted on our server and `false` otherwise
- `manage_resource_links` adds a link between some resources and a book, caused by the triggers specified. This calls `update_blobs` to re-generate the databases needed for OCR and AR detection
- `unlink_resource` separates a resource from a book if they were linked together
- `generate_preview` generates and stores a preview of the resource being added

`users.php`:
- `authenticate` will return `true` if the given username and password combination is valid and false otherwise

`publishers.php`:
- `fetch_user_publisher` will return the publisher to which a standard user belongs, or NULL if the user is an administrator

#### `utils.php`
This module contains several utility functions which are used throughout the code.

- `init` is called at the top of every script and sets up things like the session
- `is_blank`, `is_valid_url`, `is_valid_resource_display_mode`, `is_valid_isbn` and `is_pos_int` provide input validation
- `sanitise` sanitises input data to prevent XSS and SQL injection attacks
- `errors_occurred`, `add_error`, `clear_errors`, `add_notice`, `clear_notices` and `set_success` control the status (errors, notices and success message) to display to the user. `errors_occurred` returns `true` if there has been an error added
- `display_status` and `json_status` present the current status to the user - the first prints the status directly to the page and the second formats it as JSON for asynchronous requests
- `get_remote_type`, `get_type`, `get_subtype` and `get_typeclass` are used to determine MIME type of local and remote resources
- `authorised` is the central authorisation function, which given an action, returns `true` if the current user is permitted to perform it and `false` otherwise
- `db_select` is a handy function for reducing boilerplate code for `SELECT` queries
- The `*path` functions simply return the path for a certain file based on the ISBN or resource ID
- `generate_text_image` generates and outputs a PDF containing the given text
- `generate_random_string` generates a random string of the specified length
- `file_rollback`, `file_commit`, `file_ops`, `rrm` and `rcp` provide file transaction capabilities. `file_ops` is used to actuall perform the operations, `rrm` is for recursive removal and `rcp` is for recursive copy
- `rollback` and `commit` perform both database and filesystem rollbacks and commits

#### `includes.php`
This module automatically includes all the other modules in this directory so that each page only need have one `include` statement, to include this file.

#### `header.php`
This module is for creating the page headers. The `make_header` function generates a header containing all the necessary stylesheets and scripts, and has customisable `<meta>` tags

#### `footer.php`
This module is for creating the page footers and contains one function, `make_footer`, to do so

### JavaScript (`/html/assets/scripts/`)

There is only one JavaScript file, `utils.js`. It is mainly taken up by functions for submitting forms asynchronously, all of which are fairly similar and rely on the `request` function for the actual XHR. `request` sends the `FormData` object given to it and disables the button used to submit it until a response is received. Once a response is received, any errors, notices and success messages are displayed, and the page is redirected if necessary. Additionally, a callback may be specified to do something with the response data.

The other interesting function in `utils.js` is `ask_user`, which produces a custom dialog on the screen with two buttons. If the user presses "No", no action is taken, but if they press "Yes" then the callback given to the function will be called.

### CSS (`/html/assets/styles/`)

There are three stylesheets: `fonts.css`, `forms.css` and `main.css`. `fonts.css` just defines some custom fonts to use in the interface, `forms.css` contains the several styles applied to HTML form elements and `main.css` contains rules for all other elements, including containers (cards, etc.), headings, text and links.

### Database

The structure of the database is shown below. It runs using MySQL.

![zib_erd_2](https://user-images.githubusercontent.com/63247287/110464106-a1b9f580-80ca-11eb-8760-76979327d8ef.png)

### API tour

The API for the app (`/api/main.py`) is written in Python; it uses Flask and SQLAlchemy to both model and query the MySQL database. The key five endpoints are as follows:
- `/books/resources/<isbn>` returns all the resources for a particular book, specified by its ISBN.
- `/books/title/<title>` performs the server-side match; given a query string, we return possible titles and corresponding ISBNs for books the user may be intending to identify.
- `/books/resources/rid` returns a particular resource, as specified by its id.
- `/books` returns a list of all the books in the database, and associated information.
- `/books/<isbn>` is the main endpoint, which returns all information about a book (specified by ISBN), including its name, edition, ISBN, and the AR and OCR resources needed by the app.

The algorithm used to return search results is given in `/api/levenshtein.py`, using the inbuilt Python library difflib. 

### OCR tour

`/ocr/extract_pdf.py` contains code that extracts text from designated pages of a PDF, returning a JSON blob that the webserver sends to the client device. The client device can then use this for text recognition and matching. This script is called by the webserver.

## Acknowledgements

Some source code from the ARCore Android SDK is included in our android app, licensed under [Apache 2.0](https://github.com/google-ar/arcore-android-sdk). Some snippets come from the Android Documentation, also licensed under Apache 2.0

The Barcode Scanner in the app uses ZXing library, licensed under [Apache 2.0](https://github.com/journeyapps/zxing-android-embedded).

The JSON parsing in the app uses gson by Google, licensed under [Apache 2.0](https://github.com/google/gson).

The AR functionality also makes use of a pin 3D model, created by Google, licensed under [CC-BY](https://poly.google.com/view/7jrB91eIj2t).

`/html/assets/fonts` contains static files not written by us. Open Sans font licensed under Apache 2.0.

`/html/assets/images/icons/` contains icons from [IconsDB](https://www.iconsdb.com/). See https://uniform.ml/credits.php for more information.

- `/html/assets/images/icons/plus-5-128.png`, `/html/assets/images/icons/briefcase-6-128.png` and `/html/assets/images/icons/headphones-4-128.png` are licensed under CC0 1.0 Universal (CC0 1.0) Public Domain Dedication
- `/html/assets/images/icons/group-128.png`, `/html/assets/images/icons/book-stack-128.png`, `/html/assets/images/icons/web-128.png` and `/html/assets/images/icons/pages-1-128.png` are licensed under Creative Commons Attribution-NoDerivs 3.0


**This project, with the exception of open-source components as listed above, is All Rights Reserved. If you wish to use some code from it, please get in touch for permission.**
