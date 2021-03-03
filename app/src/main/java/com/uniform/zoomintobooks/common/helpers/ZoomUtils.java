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
        book.setISBN(basicInfo.getAsJsonPrimitive("isbn").getAsInt());
        book.setTitle(basicInfo.getAsJsonPrimitive("title").getAsString());
        book.setAuthor(basicInfo.getAsJsonPrimitive("author").getAsString());
        book.setARBlob(basicInfo.getAsJsonPrimitive("ar_blob").getAsString());
        book.setOCRBlob(basicInfo.getAsJsonPrimitive("ocr_blob").getAsString());
        book.setEdition(basicInfo.getAsJsonPrimitive("edition").getAsString());

        book.setPublisher(publisherInfo.getAsJsonPrimitive("publisher").getAsString());
        book.setEmail(publisherInfo.getAsJsonPrimitive("email").getAsString());

        book.setARResourceList(parseResources(details, "ar_resources"));
        book.setOCRResourceList(parseResources(details, "ocr_resources"));
        return book;

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
