package com.uniform.zoomintobooks.common.helpers;

import java.io.BufferedReader;
import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;

import android.util.Base64;

public class ZoomUtils {
    public static InputStream parse(String encodedString) {
        byte[] reply = Base64.decode(encodedString, Base64.DEFAULT);
        return new ByteArrayInputStream(reply);
    }

    public static InputStream getImageData(String imgLink) {

        try {
            URL url = new URL(imgLink);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            InputStream is = conn.getInputStream();
            return is;
        }
        catch (IOException e) {
            return null;
        }
    }

    public static void parseJSON(String resourceLink) throws IOException {
        URL url = new URL(resourceLink);
        HttpURLConnection connection = (HttpURLConnection) url.openConnection();
        connection.setRequestMethod("GET");
        connection.connect();

        BufferedReader json  = new BufferedReader(new InputStreamReader(connection.getInputStream()));

        //TEST JSON using String, TODO: remove once testing is done
        //String json = new String("{'basic_info': {'isbn': 1, 'title': 'Nausea', 'author': 'Sartre', 'ar_blob': 'c2FydHJlLmpwZ3xDOi9Vc2Vycy92aWN0by9Eb2N1bWVudHMvQ2FtYnJpZGdlL0lCL0dyb3VwIFByb2plY3QvSW1hZ2VzL3NhcnRyZS5qcGcNCg==', 'ocr_blob': 'hi', 'edition': '1st'}, 'ar_resources': [], 'publisher_info': {'publisher': 'Journal', 'email': 'j'}}");
        //JsonElement rootNode = JsonParser.parseString(json);

        JsonElement rootNode = JsonParser.parseReader(json);
        JsonObject details = rootNode.getAsJsonObject();

        JsonObject basicInfo = details.getAsJsonObject("basic_info");
        int ISBN = basicInfo.getAsJsonPrimitive("isbn").getAsInt();
        String title = basicInfo.getAsJsonPrimitive("title").getAsString();
        String author = basicInfo.getAsJsonPrimitive("author").getAsString();
        String ARBlob = basicInfo.getAsJsonPrimitive("ar_blob").getAsString();
        String OCRBlob = basicInfo.getAsJsonPrimitive("ocr_blob").getAsString();
        String edition = basicInfo.getAsJsonPrimitive("edition").getAsString();

        JsonObject publisherInfo = details.getAsJsonObject("publisher_info");
        String publisher = details.getAsJsonPrimitive("publisher").getAsString();
        String email = details.getAsJsonPrimitive("email").getAsString();

        List<BookResource> ARResourceList = parseResources(details, "ar_resources");
        List<BookResource> OCRResourceList = parseResources(details, "ocr_resources");

    }

    public static List<BookResource> parseResources(JsonObject details, String memberName) {
        JsonArray resources = details.getAsJsonArray(memberName);
        List<BookResource> bookResources = new ArrayList<>();

        for (int i = 0; i < resources.size(); ++i) {
            JsonObject resource = resources.get(i).getAsJsonObject();
            int rid = resource.getAsJsonPrimitive("rid").getAsInt();
            String url = resource.getAsJsonPrimitive("url").getAsString();
            boolean downloadable = resource.getAsJsonPrimitive("downloadable").getAsBoolean();
            String type = resource.getAsJsonPrimitive("display").getAsString();
            bookResources.add(new BookResource(rid, url, downloadable, type));
        }

        return bookResources;

    }

}
