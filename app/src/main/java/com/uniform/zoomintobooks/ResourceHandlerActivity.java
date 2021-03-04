package com.uniform.zoomintobooks;

import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.icu.util.Output;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.widget.MediaController;
import android.widget.VideoView;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;

import com.uniform.zoomintobooks.common.helpers.BookResource;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.HashMap;
import java.util.Map;

import static android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION;
import static android.content.Intent.FLAG_GRANT_WRITE_URI_PERMISSION;

/**
 * Created by Devendra K Chavan on April,2020
 */

public class ResourceHandlerActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        Intent i = getIntent();
        String uri = i.getStringExtra("url");
        String type = i.getStringExtra("type");
        String action = i.getStringExtra("action");

        byte[] bytes = i.getByteArrayExtra("file");
        String directory = i.getStringExtra("directory");

        switch (action){
            case "display":
                if(type.equals("video")){
                    OpenVideo(Uri.parse(uri),OpenResourceMode.ACTIVITY);
                }else if (type.equals("image") || type.equals("overlay")) {
                    OpenImage(Uri.parse(uri), OpenResourceMode.ACTIVITY);
                }else{
                    throw new UnsupportedOperationException("Displaying other resources not natively supported");
                }
                break;
            case "open":
                if(type.equals("video")){
                    OpenVideo(Uri.parse(uri),OpenResourceMode.ANDROID);
                }else if (type.equals("image") || type.equals("overlay")) {
                    OpenImage(Uri.parse(uri), OpenResourceMode.ANDROID);
                }else{
                    OpenResource(Uri.parse(uri), type);
                }
                break;
            case "save":
                Save(directory, bytes);
                break;
            case "export":
                Export(directory, bytes);
                break;
        }
    }

    public static Uri resourceToUri(Context context, int resID) {
        return Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" +
                context.getResources().getResourcePackageName(resID) + '/' +
                context.getResources().getResourceTypeName(resID) + '/' +
                context.getResources().getResourceEntryName(resID) );
    }

    enum OpenResourceMode{
        DEFAULT, ACTIVITY, ANDROID;
    }

    private void OpenResource(Uri uri, String type){
        Intent intent = new Intent();
        intent.setAction(Intent.ACTION_VIEW);
        intent.setData(uri);
        intent.setType(type);
        intent.setFlags(FLAG_GRANT_READ_URI_PERMISSION | FLAG_GRANT_WRITE_URI_PERMISSION);
        startActivity(intent);
    }

    private void OpenImage(Uri uri, OpenResourceMode mode){
        switch(mode){
            case DEFAULT:
            case ANDROID:
                OpenResource(uri, "image/*");
                break;
            case ACTIVITY:
                Intent i = new Intent(this, ImageActivity.class);
                i.putExtra("uri",uri);
                startActivity(i);
        }
    }

    private void OpenVideo(Uri uri, OpenResourceMode mode){
        switch(mode){
            case DEFAULT:
            case ANDROID:
                OpenResource(uri, "video/*");
                break;
            case ACTIVITY:
                Intent i = new Intent(this, VideoActivity.class);
                i.putExtra("uri",uri);
                startActivity(i);
        }
    }

    private void Save (String directory, byte bytes[]){
        FileOutputStream outputStream;

        try {
            outputStream = openFileOutput(directory, Context.MODE_PRIVATE);
            outputStream.write(bytes);
            outputStream.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void Export(String directory, byte bytes[]) {
        String state = Environment.getExternalStorageState();
        if (!Environment.MEDIA_MOUNTED.equals(state)) {
            return;
        }

        File file = new File(getExternalFilesDir(null), directory);

        FileOutputStream outputStream = null;
        try {
            file.createNewFile();
            outputStream = new FileOutputStream(file, true);
            outputStream.write(bytes);
            outputStream.flush();
            outputStream.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private FileInputStream Open(String directory) throws FileNotFoundException {
        FileInputStream inputStream = null;

        inputStream = openFileInput(directory);

        return inputStream;
    }

    private class UrlToUri<S,T> extends HashMap<S,T>{
        int filename=90;
        @Nullable
        @Override
        public T get(@Nullable Object key) {
            readFileInStore();
            return super.get(key);
        }

        @Nullable
        @Override
        public T put(S key, T value) {
            T t = super.put(key, value);
            saveFileInStore();
            return t;
        }

        public String newUri(){
            readFileInStore();
            this.filename++;
            String s = String.valueOf(filename);
            saveFileInStore();
            return s;
        }
    }
    private UrlToUri<String,String> urlToUri = new UrlToUri<String, String>();

    static String FILE_IN_STORE_DIRECTORY = "fileInStore";

    private void readFileInStore(){
        FileInputStream is = null;
        try {
            is = Open(FILE_IN_STORE_DIRECTORY);
            try {
                ObjectInputStream os = new ObjectInputStream(is);
                this.urlToUri = (UrlToUri<String, String>) os.readObject();
            } catch (IOException e) {
                e.printStackTrace();
            } catch (ClassNotFoundException e) {
                e.printStackTrace();
            }
        } catch (FileNotFoundException e) {
            byte[] bytes = {};
            Save(FILE_IN_STORE_DIRECTORY, bytes);
            e.printStackTrace();
        }
    }

    private void saveFileInStore(){
        OutputStream os = null;
        try {
            os = openFileOutput(FILE_IN_STORE_DIRECTORY, Context.MODE_PRIVATE);
            ObjectOutputStream ois = new ObjectOutputStream(os);
            ois.writeObject(urlToUri);
        } catch (FileNotFoundException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public InputStream getImageData(String imgLink) {
        InputStream is = null;
        try {
            if(urlToUri.containsKey(imgLink)){
                String uri = urlToUri.get(imgLink);
                is = Open(uri);
            }else{
                is = getImageDataNetwork(imgLink);

                // save internet file to local file
                BufferedInputStream bis = new BufferedInputStream(is);
                FileOutputStream outputStream;
                try {
                    String uri = urlToUri.newUri();
                    outputStream = openFileOutput(uri, Context.MODE_PRIVATE);
                    byte[] buf = new byte[8192];
                    int length;
                    while ((length = bis.read(buf)) > 0) {
                        outputStream.write(buf, 0, length);
                    }
                    outputStream.close();
                    bis.reset();
                    is = bis;
                    urlToUri.put(imgLink, uri);
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }
        } catch (FileNotFoundException e) {
            is = getImageDataNetwork(imgLink);
        } catch (IOException e) {
            is = getImageDataNetwork(imgLink);
        }
        return is;
    }

    public InputStream getImageDataNetwork(String imgLink) {

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
}