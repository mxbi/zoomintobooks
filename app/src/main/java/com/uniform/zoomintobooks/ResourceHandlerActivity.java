package com.uniform.zoomintobooks;

import androidx.appcompat.app.AppCompatActivity;

import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;

import com.uniform.zoomintobooks.common.helpers.UrlToUri;
import com.uniform.zoomintobooks.common.helpers.ZoomUtils;

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
import java.net.URL;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;

import static android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION;
import static android.content.Intent.FLAG_GRANT_WRITE_URI_PERMISSION;

public class ResourceHandlerActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        readFileInStore();
        saveFileInStore();

        Intent i = getIntent();
        String url = i.getStringExtra("url");
        String uri = i.getStringExtra("uri");
        String title = "";
        if(i.hasExtra("title")){
            title = i.getStringExtra("title");
        }
        String type = i.getStringExtra("type");
        String action = i.getStringExtra("action");

        switch (action){
            case "downloadAndDisplay":
                String uri_l = getUriFromUrl(url);
                String trueUri = getFilesDir().toURI().getPath().concat(uri_l);
                if(type.equals("video")){
                    OpenVideo(Uri.parse(trueUri),OpenResourceMode.ACTIVITY);
                }else if (type.equals("image")) {
                    Intent o = new Intent(this, ImageActivity.class);
                    o.putExtra("uri",Uri.parse(trueUri));
                    o.putExtra("title", title);
                    startActivity(o);
                }else if (type.equals("overlay")) {
                    Intent o = new Intent(this, ImageActivity.class);
                    o.putExtra("uri",Uri.parse(trueUri));
                    o.putExtra("title", title);
                    startActivity(o);
                }else{
                    throw new UnsupportedOperationException("Displaying other resources not natively supported");
                }
                break;
            case "downloadAndOpen":
                String uri_r = getUriFromUrl(url);
                String fileUri = getFilesDir().toURI().getPath().concat(uri_r);
                if(type.equals("video")){
                    OpenVideo(Uri.parse(fileUri),OpenResourceMode.ANDROID);
                }else if (type.equals("image") || type.equals("overlay")) {
                    OpenImage(Uri.parse(fileUri), OpenResourceMode.ANDROID);
                }else{
                    OpenResource(Uri.parse(fileUri), type);
                }
                break;
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

    private void OpenOverlay(String url){
        AsyncGetBitmap asyncGetImageData = new AsyncGetBitmap();
        Bitmap bmp = asyncGetImageData.AsyncGetImageDataOrWait(url);

        Intent i = new Intent(this, ImageActivity.class);
        i.putExtra("bitmap",bmp);

        startActivity(i);
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

    public String get(String key) {
        readFileInStore();
        return this.urlToUri.get(key);
    }

    public String put(String key, String value) {
        String t = this.urlToUri.put(key, value);
        saveFileInStore();
        return t;
    }

    public String newUriFromExt(String ext){
        readFileInStore();
        String extension ="";
        String s = this.urlToUri.newUri(ext);
        saveFileInStore();
        return s;
    }

    public String newUri(String url){
        String extension ="";
        if(url.contains(".")){
            extension = url.substring(url.lastIndexOf('.'));
        }
        String s = newUriFromExt(extension);
        return s;
    }

    public String getUriFromUrl(String url) {
        String uri = "";
        if(urlToUri.containsKey(url)){
            uri = get(url);
            return uri;
        }else{
            InputStream is = getImageDataNetwork(url);

            // save internet file to local file
            FileOutputStream outputStream;
            try {
                String newUri = newUriFromExt(".png");
                outputStream = openFileOutput(newUri, Context.MODE_PRIVATE);

                Future<OutputStream> future = new AsyncDownload().download(is, outputStream);
                while(!future.isDone()) {
                    System.out.println("Downloading...");
                    try {
                        Thread.sleep(300);
                    } catch (InterruptedException e) {
                        e.printStackTrace();
                    }
                }

                outputStream.close();
                put(url, newUri);
                return newUri;
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
        return uri;
    }

    public class AsyncGet {

        private ExecutorService executor = Executors.newSingleThreadExecutor();

        public Future<InputStream> get(String urlString) {
            return executor.submit(() -> {
                try {
                    URL url = new URL(urlString);
                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                    InputStream is = conn.getInputStream();

                    return is;
                }
                catch (IOException e) {
                    return null;
                }
            });
        }
    }

    public class AsyncDownload {
        private ExecutorService executor = Executors.newSingleThreadExecutor();

        public Future<OutputStream> download(InputStream is, OutputStream os) {
            return executor.submit(() -> {
                byte[] buf = new byte[8192];
                int length = 0;
                while (true) {
                    try {
                        if (!((length = is.read(buf)) > 0)) break;
                    } catch (IOException e) {
                        e.printStackTrace();
                    }
                    os.write(buf, 0, length);
                }
                return os;
            });
        }
    }

    public class AsyncGetBitmap {

        private ExecutorService executor = Executors.newSingleThreadExecutor();

        public Future<Bitmap> getBitmap(String urlString) {
            return executor.submit(() -> {
                InputStream is = ZoomUtils.getImageData("https://uniform.ml/console/resources/resource/upload/?rid=25");

                return BitmapFactory.decodeStream(is);
            });
        }

        public Bitmap AsyncGetImageDataOrWait(String url){
            Future<Bitmap> future = getBitmap(url);
            while(!future.isDone()) {
                System.out.println("Getting image...");
                try {
                    Thread.sleep(300);
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }
            Bitmap bmp = null;
            try {
                bmp = future.get();
                return bmp;
            } catch (ExecutionException e) {
                e.printStackTrace();
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
            return bmp;
        }

    }

    public InputStream getImageDataNetwork(String imgLink) {
        Future<InputStream> future = new AsyncGet().get(imgLink);
        while(!future.isDone()) {
            System.out.println("Getting image...");
            try {
                Thread.sleep(300);
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }
        InputStream is = null;
        try {
            is = future.get();
        } catch (ExecutionException e) {
            e.printStackTrace();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
        return is;
    }
}