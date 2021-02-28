package com.zoomintobooks;

import androidx.annotation.RequiresApi;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.FileProvider;

import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;

import static android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION;
import static android.content.Intent.FLAG_GRANT_WRITE_URI_PERMISSION;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        //Intent i = new Intent(this, VideoActivity.class);
        //startActivity(i);


        OpenImage("android.resource://"+getPackageName()+"/"+R.raw.testimage, OpenResourceMode.DEFAULT);


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
}