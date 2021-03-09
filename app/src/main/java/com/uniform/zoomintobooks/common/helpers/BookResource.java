package com.uniform.zoomintobooks.common.helpers;

import java.io.Serializable;

public class BookResource implements Serializable {
    private int rid;
    private String url;
    private boolean downloadable;
    private String display;
    private String title;
    private String ocrPageNumber;

    public BookResource(int rid, String url, boolean downloadable, String display, String title) {
        this.rid = rid;
        this.url = url;
        this.downloadable = downloadable;
        this.display = display;
        this.title = title;
    }

    public BookResource(int rid, String url, boolean downloadable, String display, String title, String ocrPageNumber) {
        this.rid = rid;
        this.url = url;
        this.downloadable = downloadable;
        this.display = display;
        this.title = title;
        this.ocrPageNumber = ocrPageNumber;
    }

    public BookResource(BookResource bookResource){
        this.rid = bookResource.rid;
        this.url = bookResource.url;
        this.downloadable = bookResource.downloadable;
        this.display = bookResource.display;
        this.title = bookResource.title;
        this.ocrPageNumber = bookResource.ocrPageNumber;
    }

    public String getURL() {
        return url;
    }

    public String getOcrPageNumber() {
        return ocrPageNumber;
    }

    public void setURL(String url){
        this.url = url;
    }

    public boolean isOverlayable() {
        return display.equals("overlay");
    }

    public String getType(){
        return display;
    }

    public String getTitle() { return title; }
}
