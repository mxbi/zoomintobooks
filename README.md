# uniform

- `api`: Contains our back-end API code, written in Flask & Python
- `html`: Contains our back-end web console code, written in PHP & HTML5
- `app`: Contains the code of our Android client, written in Java (with some GLSL)
- `ocr`: Contains the server-side code for OCR scanning, written in Python.

## Android App Tour

#### `\app\src\main\java\com\uniform\zoomintobooks`

**@everyone please fill in, feel free to add more lines for e.g. helper classes as many of them are significant**

- AugmentedImageActivity.java

The main activity that performs AR and OCR image recognition.

- BarcodeScanActivity.java

The activity that scans a barcode and returns its numerical decoding.

- BookActivity.java

- ContactActivity.java

- ImageActivity.java

- InfoActivity.java

- ListViewActivity.java

- ResourceHandlerActivity.java

- SelectBookActivity.java

- SettingsActivity.java

Allows the user to switch between Light and Dark Mode. Provides framework for 2 other settings, should they be implemented in the future.

- VideoActivity.java

- WebViewActivity.java

Allows for opening arbitrary linked resources by URL, and displaying them to the user.

- WelcomeActivity.java


**See also** `\app\src\main\java\com\uniform\zoomintobooks\common\helpers`, which contains many helper classes used within the activities above.

## Web tour

### Pages (`/html/`)

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
- `authenticate`

`publishers.php`:
- `fetch_user_publisher`

#### `utils.php`
This module contains several utility functions which are used throughout the code.

#### `includes.php`
This module automatically includes all the other modules in this directory so that each page only need have one `include` statement, to include this file.

#### `header.php`
This module is for creating the page headers. The `make_header` function generates a header containing all the necessary stylesheets and scripts, and has customisable `<meta>` tags

#### `footer.php`
This module is for creating the page footers. 

### JavaScript (`/html/assets/scripts/`)

There is only one JavaScript file, `utils.js`. It is mainly taken up by functions for submitting forms asynchronously, all of which are fairly similar and rely on the `request` function for the actual XHR. `request` sends the `FormData` object given to it and disables the button used to submit it until a response is received. Once a response is received, any errors, notices and success messages are displayed, and the page is redirected if necessary. Additionally, a callback may be specified to do something with the response data.

The other interesting function in `utils.js` is `ask_user`, which produces a custom dialog on the screen with two buttons. If the user presses "No", no action is taken, but if they press "Yes" then the callback given to the function will be called.

### CSS (`/html/assets/styles/`)

There are three stylesheets: `fonts.css`, `forms.css` and `main.css`. `fonts.css` just defines some custom fonts to use in the interface, `forms.css` contains the several styles applied to HTML form elements and `main.css` contains rules for all other elements, including containers (cards, etc.), headings, text and links.

### Database

The structure of the database is shown below. It runs using MySQL.

![zib_erd_2](https://user-images.githubusercontent.com/63247287/110464106-a1b9f580-80ca-11eb-8760-76979327d8ef.png)

### API tour

**@victoria, probably can be short lol**

### OCR tour

`/ocr/extract_pdf.py` contains code that extracts text from designated pages of a PDF, returning a JSON blob that the webserver sends to the client device. The client device can then use this for text recognition and matching. This script is called by the webserver.

### Acknowledgements

Some source code from the ARCore Android SDK is included in our android app, licensed under [Apache 2.0](https://github.com/google-ar/arcore-android-sdk). Some snippets come from the Android Documentation, also licensed under Apache 2.0

The Barcode Scanner in the app uses ZXing library, licensed under [Apache 2.0](https://github.com/journeyapps/zxing-android-embedded).

`/html/assets/fonts` contains static files not written by us. Open Sans font licensed under Apache 2.0. Proza Libre licensed under SIL Open Font Licence 1.1. 

`/html/assets/images/icons/` contains icons from [IconsDB](https://www.iconsdb.com/)

`/html/assets/images/icons/plus-5-128.png`, `/html/assets/images/icons/briefcase-6-128.png` and `/html/assets/images/icons/headphones-4-128.png` are licensed under CC0 1.0 Universal (CC0 1.0) Public Domain Dedication
`/html/assets/images/icons/group-128.png`, `/html/assets/images/icons/book-stack-128.png`, `/html/assets/images/icons/web-128.png` and `/html/assets/images/icons/pages-1-128.png` are licensed under Creative Commons Attribution-NoDerivs 3.0

**@everyone please add to this if applicable**
