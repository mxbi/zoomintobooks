package com.uniform.zoomintobooks.common.helpers;

import android.os.AsyncTask;
import android.os.Build;

import androidx.annotation.RequiresApi;

import com.uniform.zoomintobooks.AugmentedImageActivity;

import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;

//public class AsyncGetImageData extends AsyncTask<String, InputStream, InputStream> {
//    private String imgLink;
//    private AugmentedImageActivity augmentedImageActivity;
//
//    public void setImgLink(String imgLink){
//        this.imgLink = imgLink;
//    }
//
//    public void setAugmentedImageActivity(AugmentedImageActivity augmentedImageActivity){
//        this.augmentedImageActivity = augmentedImageActivity;
//    }
//
//    @RequiresApi(api = Build.VERSION_CODES.N)
//    @Override
//    protected InputStream doInBackground(String... strings) {
//        return getImageData(this.imgLink);
//    }
//
//    @Override
//    protected void onPostExecute(InputStream result) {
//        this.augmentedImageActivity.resourceDisplay(result);
//    }
//
//    public static InputStream getImageData(String imgLink) {
//
//        try {
//            URL url = new URL(imgLink);
//            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
//            InputStream is = conn.getInputStream();
//            return is;
//        }
//        catch (IOException e) {
//            return null;
//        }
//    }
//}
