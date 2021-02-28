package com.zoomintobooks;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.view.View;
import android.widget.MediaController;

import androidx.appcompat.app.AppCompatActivity;

import java.net.URI;

public class VideoActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        PlayVideoFromFile("android.resource://"+getPackageName()+"/"+R.raw.testvideo);
    }

    public void PlayVideoFromFile(String uriPath){
        PlayVideo(Uri.parse(uriPath));
    }

    public void PlayVideo(Uri uri){
        Intent intent = new Intent(Intent.ACTION_VIEW);
        intent.setDataAndType(uri, "video/*");
        startActivity(intent);
    }
}
