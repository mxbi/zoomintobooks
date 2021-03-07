var selectedResources = new Set();
var selectedImages = new Set();
var triggerImgCount = 0;
var resourceLinkType = "url";

function isPositiveInteger(n) {
    return n >>> 0 === parseFloat(n);
}

function toggleResourceSelect(rid) {
    var card = document.getElementById("selectable-resource-" + rid);
    if (selectedResources.has(rid)) {
        selectedResources.delete(rid);
        card.classList.remove("selected-card-list-item");
    } else {
        selectedResources.add(rid);
        card.classList.add("selected-card-list-item");
    }
}

function displayErrors(parent, errors) {
    var errorsContainer = document.createElement("div");
    errorsContainer.classList.add("errors");
    var errorsList = document.createElement("ul");
    errorsContainer.append(errorsList);
    for (var error of errors) {
        var errorItem = document.createElement("li");
        errorItem.textContent = error;
        errorsContainer.append(errorItem);
    }
    parent.prepend(errorsContainer);
}

function displayNotices(parent, notices) {
    var noticesContainer = document.createElement("div");
    noticesContainer.classList.add("notices");
    var noticesList = document.createElement("ul");
    noticesContainer.append(noticesList);
    for (var notice of notices) {
        var noticeItem = document.createElement("li");
        noticeItem.textContent = notice;
        noticesContainer.append(noticeItem);
    }
    parent.prepend(noticesContainer);
}

function displaySuccess(parent, success) {
    var successContainer = document.createElement("div");
    successContainer.classList.add("success");
    successContainer.textContent = success;
    parent.prepend(successContainer);
}

function displayStatus(status) {
    clearStatus();
    var mainElement = document.querySelector("main");
    if (status.errors !== undefined && status.errors.length > 0) {
        displayErrors(mainElement, status.errors);
    }
    if (status.notices !== undefined && status.notices.length > 0) {
        displayNotices(mainElement, status.notices);
    }
    if (status.success !== undefined && status.success !== "") {
        displaySuccess(mainElement, status.success);
    }
}

function clearStatus() {
    var errors = document.getElementsByClassName("errors");
    var notices = document.getElementsByClassName("notices");
    var success = document.getElementsByClassName("success");
    while (errors[0]) errors[0].parentNode.removeChild(errors[0]);
    while (notices[0]) notices[0].parentNode.removeChild(notices[0]);
    while (success[0]) success[0].parentNode.removeChild(success[0]);
}

function removeUnlinkedResource(response, args) {
    var unlinkedResource = document.getElementById("resource-container-" + args["rid"]);
    unlinkedResource.parentNode.removeChild(unlinkedResource);
}

function request(triggerId, action, data, callback="", args={}) {
    var trigger = document.getElementById(triggerId);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", action, true);
    xhr.onreadystatechange = function () {
        if (this.readyState == 4) {
            trigger.disabled = false;
            console.log(this.response);
            var status = JSON.parse(this.response);
            if (status.redirect !== undefined && status.redirect !== "") {
                window.location.href = status.redirect;
            } else {
                if (callback != "") {
                    window[callback](this.response, args);
                }
                displayStatus(JSON.parse(this.response));
            }
        }
    };
    trigger.disabled = true;
    xhr.send(data);
}

function askUser(msg, callback, args) {
    var body = document.body;
    body.style.overflow = "hidden";
    var dialogBackground = document.createElement("div");
    dialogBackground.classList.add("dialog-bg");
    var dialogBox = document.createElement("div");
    dialogBox.classList.add("dialog");
    var msgP = document.createElement("p");
    msgP.textContent = msg;
    var yesBtn = document.createElement("button");
    yesBtn.textContent = "Yes";
    yesBtn.classList.add("yes-btn");
    yesBtn.onclick = function () { dialogBackground.parentNode.removeChild(dialogBackground); window[callback](args); body.style.overflow = "auto"; };
    var noBtn = document.createElement("button");
    noBtn.textContent = "No";
    noBtn.classList.add("no-btn");
    noBtn.onclick = function () { dialogBackground.parentNode.removeChild(dialogBackground); body.style.overflow = "auto"; };
    dialogBox.appendChild(msgP);
    dialogBox.appendChild(noBtn);
    dialogBox.appendChild(yesBtn);
    dialogBackground.appendChild(dialogBox);
    body.appendChild(dialogBackground);
}

function submitResourceLink() {
    var triggerImagesInput = document.getElementById("trigger-images-input");
    var pagesInput = document.getElementById("pages-input");
    var isbnInput = document.getElementById("isbn-input");
    var fileCount = triggerImagesInput.files.length;

    if (selectedResources.size > 0 && (fileCount > 0 || pagesInput.value != "")) {
        var data = new FormData();
        for (var i = 0; i < fileCount; i++) {
            data.append("trigger_images[]", triggerImagesInput.files[i]);
        }

        data.append("pages", pagesInput.value);
        data.append("isbn", isbnInput.value);

        for (var resource of selectedResources) {
            data.append("resources[]", resource);
        }

        request("resource-link-btn", "action.php", data);
    } else {
        var errors = [];
        if (selectedResources.size == 0) {
            errors.push("You must select at least one resource to link");
        }
        if (fileCount == 0 && pagesInput.value == "") {
            errors.push("You must select at least one trigger (images or pages)")
        }
        clearStatus();
        displayErrors(document.querySelector("main"), errors);
    }
}

function updateBlobs(isbn) {
    var data = new FormData();
    data.append("isbn", isbn);
    request("update-triggers-btn", "update_blobs.php", data);
}

function manageBook() {
    var isbnInput = document.getElementById("isbn-input");
    var newIsbnInput = document.getElementById("new-isbn-input");
    var titleInput = document.getElementById("title-input");
    var authorInput = document.getElementById("author-input");
    var publisherInput = document.getElementById("publisher-input");
    var editionInput = document.getElementById("edition-input");
    var bookInput = document.getElementById("book-input");

    var data = new FormData();
    data.append("book", bookInput.files[0]);
    data.append("isbn", isbnInput.value);
    if (newIsbnInput) data.append("new_isbn", newIsbnInput.value);
    data.append("title", titleInput.value);
    data.append("author", authorInput.value);
    data.append("publisher", publisherInput.value);
    data.append("edition", editionInput.value);
    request("manage-book-btn", "action.php", data);
}

function manageResource() {
    var ridInput = document.getElementById("rid-input");
    var nameInput = document.getElementById("name-input");
    var urlInput = document.getElementById("url-input");
    var resourceInput = document.getElementById("resource-input");
    var displayInput = document.getElementById("display-input");
    var downloadableInput = document.getElementById("downloadable-input");

    var data = new FormData();
    data.append("rid", ridInput.value);
    data.append("name", nameInput.value);
    if (resourceLinkType == "url") {
        data.append("url", urlInput.value);
    } else {
        data.append("resource", resourceInput.files[0]);
    }
    data.append("display", displayInput.value);
    data.append("downloadable", downloadableInput.value);
    request("manage-resource-btn", "action.php", data);
}

function manageUser() {
    var usernameInput = document.getElementById("username-input");
    var newUsernameInput = document.getElementById("new-username-input");
    var passwordInput = document.getElementById("password-input");
    var password2Input = document.getElementById("password2-input");
    var publisherInput = document.getElementById("publisher-input");

    var data = new FormData();
    data.append("username", usernameInput.value);
    if (newUsernameInput) data.append("new_username", newUsernameInput.value);
    data.append("password", passwordInput.value);
    data.append("password2", password2Input.value);
    if (publisherInput) data.append("publisher", publisherInput.value);
    request("manage-user-btn", "action.php", data);
}

function managePublisher() {
    var publisherInput = document.getElementById("publisher-input");
    var newPublisherInput = document.getElementById("new-publisher-input");
    var emailInput = document.getElementById("email-input");

    var data = new FormData();
    data.append("publisher", publisherInput.value);
    if (newPublisherInput) data.append("new_publisher", newPublisherInput.value);
    data.append("email", emailInput.value);
    request("manage-publisher-btn", "action.php", data);
}

function unlinkResource(args) {
    var isbn = args["isbn"];
    var rid = args["rid"];
    var data = new FormData();
    data.append("isbn", isbn);
    data.append("rid", rid);
    request("unlink-resource-btn-" + rid, "unlink.php", data, "removeUnlinkedResource", {"rid": rid});
}

function showUrlInputContainer() {
    var urlInputTab = document.getElementById("url-input-tab");
    var urlInputContainer = document.getElementById("url-input-container");
    var resourceInputTab = document.getElementById("resource-input-tab");
    var resourceInputContainer = document.getElementById("resource-input-container");
    resourceLinkType = "url";
    urlInputTab.classList.add("active-tab")
    resourceInputTab.classList.remove("active-tab")
    urlInputContainer.style.display = "block";
    resourceInputContainer.style.display = "none";
    urlInput.required = true;
    resourceInput.required = false;
}

function showResourceInputContainer() {
    var urlInputTab = document.getElementById("url-input-tab");
    var urlInputContainer = document.getElementById("url-input-container");
    var urlInput = document.getElementById("url-input");
    var resourceInputTab = document.getElementById("resource-input-tab");
    var resourceInputContainer = document.getElementById("resource-input-container");
    var resourceInput = document.getElementById("resource-input");
    resourceLinkType = "upload";
    urlInputTab.classList.remove("active-tab")
    resourceInputTab.classList.add("active-tab")
    urlInputContainer.style.display = "none";
    resourceInputContainer.style.display = "block";
    urlInput.required = false;
    resourceInput.required = true;
}

function deleteResource(args) {
    var rid = args["rid"];
    var data = new FormData();
    data.append("rid", rid);
    request("resource-delete-btn", "delete.php", data);
}
