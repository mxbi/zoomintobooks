var selectedResources = new Set();
var selectedImages = new Set();
var triggerImgCount = 0;

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

function toggleImageSelect(img) {
    console.log(img);
    var card = document.getElementById("selectable-image-" + img);
    if (selectedImages.has(img)) {
        selectedImages.delete(img);
        card.classList.remove("selected-card-list-item");
    } else {
        selectedImages.add(img);
        card.classList.add("selected-card-list-item");
    }
}

function addTriggerImage() {
    var container = document.getElementById("trigger-images-container");
    var triggerImageContainer = document.createElement("div");
    var triggerImageInput = document.createElement("input");
    var removeTriggerImageBtn = document.createElement("button");

    triggerImageInput.type = "file";

    {
        var img = triggerImgCount;
        removeTriggerImageBtn.addEventListener('click', function(){
            removeTriggerImage(img);
        });
    }
    removeTriggerImageBtn.textContent = "Remove";
    removeTriggerImageBtn.type = "button";

    triggerImageContainer.id = "trigger-image-container-" + (triggerImgCount++);

    triggerImageContainer.appendChild(triggerImageInput);
    triggerImageContainer.appendChild(removeTriggerImageBtn);
    container.appendChild(triggerImageContainer);
}

function removeTriggerImage(img) {
    var triggerImageContainer = document.getElementById("trigger-image-container-" + img);
    triggerImageContainer.parentNode.removeChild(triggerImageContainer);
}

function submitResourceLink() {
    var triggerImagesInput = document.getElementById("trigger-images-input");
    var pagesInput = document.getElementById("pages-input");
    var isbnInput = document.getElementById("isbn-input");
    var fileCount = triggerImagesInput.files.length;

    // TODO: proper client-side validation
    if (selectedResources.size > 0 && (fileCount > 0 || pagesInput.value != "")) {

        var formData = new FormData();

        for (var i = 0; i < fileCount; i++) {
            formData.append("trigger_images[]", triggerImagesInput.files[i]);
        }

        formData.append("pages", pagesInput.value);
        formData.append("isbn", isbnInput.value);

        for (var resource of selectedResources) {
            formData.append("resources[]", resource);
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "action.php", true);
        xhr.onreadystatechange = function () {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    location.reload();
                } else {
                    // TODO: show errors more effectively
                    location.reload();
                }
            }
        };
        xhr.send(formData);
    } else {
        window.alert("Please select a file");
    }
}

function updateBlobs(isbn) {
    var formData = new FormData();

    formData.append("isbn", isbn);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_blobs.php", true);
    xhr.onreadystatechange = function () {
        if (this.readyState == 4) {
            var response = this.response;
            console.log(response);
            if (this.status == 200) {
                //location.reload();
            } else {
                // TODO: show errors more effectively
                //location.reload();
            }
        }
    };
    xhr.send(formData);
}
