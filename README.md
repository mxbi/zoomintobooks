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
- `/html/assets/modules/books.php`
- `/html/assets/modules/resources.php`
- `/html/assets/modules/users.php`
- `/html/assets/modules/publishers.php`
- `/html/assets/modules/utils.php`
- `/html/assets/modules/includes.php`
- `/html/assets/modules/header.php`
- `/html/assets/modules/footer.php`

### JavaScript (`/html/assets/scripts/`)

### CSS (`/html/assets/styles/`)

### Database

![zib_erd_2](https://user-images.githubusercontent.com/63247287/110464106-a1b9f580-80ca-11eb-8760-76979327d8ef.png)

### API tour

**@victoria, probably can be short lol**

### OCR tour

`/ocr/extract_pdf.py` contains code that extracts text from designated pages of a PDF, returning a JSON blob that the webserver sends to the client device. The client device can then use this for text recognition and matching. This script is called by the webserver.

### Acknowledgements

Some source code from the ARCore Android SDK is included in our android app, licensed under [Apache 2.0](https://github.com/google-ar/arcore-android-sdk). Some snippets come from the Android Documentation, also licensed under Apache 2.0

`/html/assets/fonts` contains static files not written by us. Open Sans font licensed under Apache 2.0. Proza Libre licensed under SIL Open Font Licence 1.1. 

`/html/assets/images/icons/` contains icons from [IconsDB](https://www.iconsdb.com/)

`/html/assets/images/icons/plus-5-128.png`, `/html/assets/images/icons/briefcase-6-128.png` and `/html/assets/images/icons/headphones-4-128.png` are licensed under CC0 1.0 Universal (CC0 1.0) Public Domain Dedication
`/html/assets/images/icons/group-128.png`, `/html/assets/images/icons/book-stack-128.png`, `/html/assets/images/icons/web-128.png` and `/html/assets/images/icons/pages-1-128.png` are licensed under Creative Commons Attribution-NoDerivs 3.0

**@everyone please add to this if applicable**
