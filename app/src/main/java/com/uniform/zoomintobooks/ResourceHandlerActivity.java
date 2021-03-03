package com.uniform.zoomintobooks;

import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.widget.MediaController;
import android.widget.VideoView;

import androidx.appcompat.app.AppCompatActivity;

import com.uniform.zoomintobooks.common.helpers.BookResource;

import java.io.File;
import java.io.FileOutputStream;

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
        String uri = i.getStringExtra("uri");
        String type = i.getStringExtra("type");
        String action = i.getStringExtra("action");

        byte[] bytes = i.getByteArrayExtra("file");
        String directory = i.getStringExtra("directory");

        switch (action){
            case "display":
                if(type.equals("video")){
                    OpenVideo(Uri.parse(uri),OpenResourceMode.ACTIVITY);
                }else if (type.equals("image")) {
                    OpenImage(Uri.parse(uri), OpenResourceMode.ACTIVITY);
                }else{
                    throw new UnsupportedOperationException("Displaying other resources not natively supported");
                }
            case "open":
                if(type.equals("video")){
                    OpenVideo(Uri.parse(uri),OpenResourceMode.ANDROID);
                }else if (type.equals("image")) {
                    OpenImage(Uri.parse(uri), OpenResourceMode.ANDROID);
                }else{
                    OpenResource(Uri.parse(uri), type);
                }
            case "save":
                Save(directory, bytes);
            case "export":
                Export(directory, bytes);
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
}