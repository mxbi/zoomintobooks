package com.uniform.zoomintobooks.common.helpers;

import android.content.Intent;

import com.google.gson.JsonObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class BookInfo {
    private String ISBN;
    private String title;

    private String author;
    private String ARBlob;
    private String OCRBlob;
    private String edition;

    private String publisher;
    private String email;

    private List<BookResource> ARResourceList;
    private List<BookResource> OCRResourceList;

    public void setISBN(String isbn) {
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

    public String getTitle() {
        return title;
    }

    public String getISBN() {
        return ISBN;
    }

    public String getAuthor() {
        return author;
    }

    public String getARBlob() {
        return ARBlob;
    }

    public String getOCRBlob() {
        return OCRBlob;
    }

    public String getEdition() {
        return edition;
    }

    public String getPublisher() {
        return publisher;
    }

    public String getEmail() {
        return email;
    }

    public List<BookResource> getARResourceList() {
        return ARResourceList;
    }

    public List<BookResource> getOCRResourceList() {
        return OCRResourceList;
    }

    // Passing between activities
    public void addAllToIntent(Intent startIntent) {
        startIntent.putExtra("book_isbn", getISBN());
        startIntent.putExtra("book_title", getTitle());
        startIntent.putExtra("book_author", getAuthor());
        startIntent.putExtra("book_ARBlob", getARBlob());
        startIntent.putExtra("book_OCRBlob", getOCRBlob());
        startIntent.putExtra("book_edition", getEdition());
        startIntent.putExtra("book_publisher", getPublisher());
        startIntent.putExtra("book_email", getEmail());
        startIntent.putExtra("book_ARResourceList", (ArrayList<BookResource>) getARResourceList());
        startIntent.putExtra("book_OCRResourceList", (ArrayList<BookResource>) getOCRResourceList());
    }
}
