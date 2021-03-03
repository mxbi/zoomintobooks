package com.uniform.zoomintobooks.common.helpers;

import com.google.gson.JsonObject;

import java.util.List;

public class BookInfo {
    private int ISBN;
    private String title;
    private String author;
    private String ARBlob;
    private String OCRBlob;
    private String edition;

    private String publisher;
    private String email;

    private List<BookResource> ARResourceList;
    private List<BookResource> OCRResourceList;

    public String getTitle() {
        return title;
    }

    public void setISBN(int isbn) {
        this.ISBN = isbn;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public void setAuthor(String author) {
        this.author = author;
    }

    public void setARBlob(String ar_blob) {
        this.ARBlob = ar_blob;
    }

    public void setOCRBlob(String ocr_blob) {
        this.OCRBlob = ocr_blob;
    }

    public void setEdition(String edition) {
        this.edition = edition;
    }

    public void setPublisher(String publisher) {
        this.publisher = publisher;
    }

    public void setARResourceList(List<BookResource> ar_resources) {
        this.ARResourceList = ar_resources;
    }

    public void setOCRResourceList(List<BookResource> ocr_resources) {
        this.OCRResourceList = ocr_resources;
    }

    public void setEmail(String email) {
        this.email = email;
    }
}
