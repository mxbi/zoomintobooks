package com.uniform.zoomintobooks.common.helpers;

import java.io.Serializable;

public class BookResource implements Serializable {
    private int rid;
    private String url;
    private boolean downloadable;
    private String display;
    private String type;

    public BookResource(int rid, String url, boolean downloadable, String display, String type) {
        this.rid = rid;
        this.url = url;
        this.downloadable = downloadable;
        this.display = display;
    }

    public BookResource(BookResource bookResource){
        this.rid = bookResource.rid;
        this.url = bookResource.url;
        this.downloadable = bookResource.downloadable;
        this.display = bookResource.display;
        this.type = bookResource.type;
    }

    public String getURL() {
        return url;
    }

    public String setURL(String url){
        this.url = url;
    }

    public boolean isOverlayable() {
        return display.equals("overlay");
    }

    public String getType(){
        return type;
    }
