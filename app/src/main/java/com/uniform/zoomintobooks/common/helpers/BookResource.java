package com.uniform.zoomintobooks.common.helpers;

public class BookResource {
    private int rid;
    private String url;
    private boolean downloadable;
    private String display;

    public BookResource(int rid, String url, boolean downloadable, String display) {
        this.rid = rid;
        this.url = url;
        this.downloadable = downloadable;
        this.display = display;
    }

    public String getURL() {
        return url;
    }

    public boolean isOverlayable() {
        return display.equals("overlay");
    }
}
