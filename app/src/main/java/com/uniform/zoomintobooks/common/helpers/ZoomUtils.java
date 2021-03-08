package com.uniform.zoomintobooks.common.helpers;

import java.io.BufferedReader;
import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;

import android.util.ArrayMap;
import android.util.Base64;
import android.util.Log;

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

    public static BookInfo parseJSON(String resourceLink) throws IOException {
        URL url = new URL(resourceLink);
        HttpURLConnection connection = (HttpURLConnection) url.openConnection();
        connection.setRequestMethod("GET");
        connection.connect();

        BufferedReader json = new BufferedReader(new InputStreamReader(connection.getInputStream()));

        JsonElement rootNode = JsonParser.parseReader(json);
        JsonObject details = rootNode.getAsJsonObject();

        JsonObject basicInfo = details.getAsJsonObject("basic_info");
        JsonObject publisherInfo = details.getAsJsonObject("publisher_info");
        BookInfo book = new BookInfo();
        book.setISBN(basicInfo.getAsJsonPrimitive("isbn").getAsString());
        book.setTitle(basicInfo.getAsJsonPrimitive("title").getAsString());
        book.setAuthor(basicInfo.getAsJsonPrimitive("author").getAsString());
        try {
            book.setARBlob(basicInfo.getAsJsonPrimitive("ar_blob").getAsString());
        } catch (ClassCastException e) {
            Log.w("[ZoomUtils]", "No AR Blob provided");
            book.setARBlob(null);
        }
        try {
            book.setOCRBlob(basicInfo.getAsJsonPrimitive("ocr_blob").getAsString());
        } catch (ClassCastException e) {
            Log.w("[ZoomUtils]", "No OCR Blob provided");
            book.setOCRBlob(null);
        }
        book.setEdition(basicInfo.getAsJsonPrimitive("edition").getAsString());

        book.setPublisher(publisherInfo.getAsJsonPrimitive("publisher").getAsString());
        book.setEmail(publisherInfo.getAsJsonPrimitive("email").getAsString());

        book.setARResourceList(parseResources(details, "ar_resources", false));
        book.setOCRResourceList(parseResources(details, "ocr_resources", true));
        return book;

    }

    public static List<BookResource> parseResources(JsonObject details, String memberName, boolean isOCRResource) {
        JsonArray resources = details.getAsJsonArray(memberName);
        List<BookResource> bookResources = new ArrayList<>();

        for (int i = 0; i < resources.size(); ++i) {
            JsonObject resource = resources.get(i).getAsJsonObject();
            int rid = resource.getAsJsonPrimitive("rid").getAsInt();
            String url = resource.getAsJsonPrimitive("url").getAsString();
            boolean downloadable = resource.getAsJsonPrimitive("downloadable").getAsBoolean();
            String display = resource.getAsJsonPrimitive("display").getAsString();
            String title = resource.getAsJsonPrimitive("name").getAsString();

            if (isOCRResource) {
                String pageNumber = resource.getAsJsonPrimitive("page").getAsString();
                bookResources.add(new BookResource(rid, url, downloadable, display, title, pageNumber));
            } else {
                bookResources.add(new BookResource(rid, url, downloadable, display, title));
            }

        }

        return bookResources;

    }

    public static ArrayMap<String, String> parseJSONlist(String resourceLink) throws IOException {
        ArrayMap<String, String> booklist = new ArrayMap<String, String>();
        URL url = new URL(resourceLink);
        HttpURLConnection connection = (HttpURLConnection) url.openConnection();
        connection.setRequestMethod("GET");
        connection.connect();

        BufferedReader json = new BufferedReader(new InputStreamReader(connection.getInputStream()));

        JsonElement rootNode = JsonParser.parseReader(json);
        JsonObject details = rootNode.getAsJsonObject();

        JsonArray results = details.getAsJsonArray("results");
        for(int i=0; i<results.size();i++){
            JsonArray s = results.get(i).getAsJsonArray();
            String Title = s.get(0).getAsString();
            String isbn = s.get(1).getAsString();
            booklist.put(isbn,Title);
        }
        return booklist;
    }
}
