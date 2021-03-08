package com.uniform.zoomintobooks.common.helpers;

import java.io.Serializable;

public class BookResource implements Serializable {
    private int rid;
    private String url;
    private boolean downloadable;
    private String display;

    private String ocrPageNumber;

    public BookResource(int rid, String url, boolean downloadable, String display) {
        this.rid = rid;
        this.url = url;
        this.downloadable = downloadable;
        this.display = display;
    }

    public BookResource(int rid, String url, boolean downloadable, String display, String ocrPageNumber) {
        this.rid = rid;
        this.url = url;
        this.downloadable = downloadable;
        this.display = display;
        this.ocrPageNumber = ocrPageNumber;
    }

    public BookResource(BookResource bookResource){
        this.rid = bookResource.rid;
        this.url = bookResource.url;
        this.downloadable = bookResource.downloadable;
        this.display = bookResource.display;
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
}
