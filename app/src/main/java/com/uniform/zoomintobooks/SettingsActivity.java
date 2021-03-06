package com.uniform.zoomintobooks;


import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
//import android.view.MenuItem;
//import android.view.View;

//import androidx.annotation.NonNull;
import androidx.appcompat.app.ActionBar;
import androidx.appcompat.app.AppCompatActivity;
// import androidx.preference.Preference;
import androidx.preference.Preference;
import androidx.preference.PreferenceFragmentCompat;
//import androidx.preference.PreferenceManager;
//import androidx.preference.SeekBarPreference;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.AsyncTask;
import androidx.annotation.NonNull;
import android.util.Log;
import java.io.BufferedInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLConnection;
import java.util.HashSet;
import java.util.Set;
import androidx.appcompat.app.ActionBar;
import androidx.appcompat.app.AppCompatActivity;
import androidx.preference.SwitchPreference;
import androidx.preference.SwitchPreferenceCompat;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import com.google.android.material.floatingactionbutton.FloatingActionButton;

public class SettingsActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);
        if (savedInstanceState == null) {
            getSupportFragmentManager()
                    .beginTransaction()
                    .replace(R.id.settings, new SettingsFragment())
                    .commit();
        }


        FloatingActionButton ReturnButton = findViewById(R.id.Return);
        ReturnButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(view.getContext(), (Class) getIntent().getExtras().get("CurrentAct"));
            startActivity(startIntent);
        });

        ActionBar actionBar = getSupportActionBar();
        if (actionBar != null) {
            actionBar.setDisplayHomeAsUpEnabled(true);
        }

        //necessary? setupSharedPreferences();
    }

    void dosthwithsetting(){


    }

    /* public static class BasicImageDownloader {
        public boolean active;

        private OnImageLoaderListener mImageLoaderListener;
        private Set<String> mUrlsInProgress = new HashSet<>();
        private final String TAG = this.getClass().getSimpleName();

        public BasicImageDownloader(@NonNull OnImageLoaderListener listener) {
            this.mImageLoaderListener = listener;
            this.active = false;
        }

        public interface OnImageLoaderListener {
            void onError(ImageError error);
            void onProgressChange(int percent);
            void onComplete(Bitmap result);
        }

        public boolean downloadable() {
            if (findViewById(R.id.auto_download)) {
                return false;
            }
            return true;
        }*/

        /* public void download(@NonNull final String imageUrl, final boolean displayProgress) {
            if (mUrlsInProgress.contains(imageUrl)) {
                Log.w(TAG, "a download for this url is already running, " +
                        "no further download will be started");
                return;
            }

            new AsyncTask<Void, Integer, Bitmap>() {

                private ImageError error;

                @Override
                protected void onPreExecute() {
                    mUrlsInProgress.add(imageUrl);
                    Log.d(TAG, "starting download");
                }

                @Override
                protected void onCancelled() {
                    mUrlsInProgress.remove(imageUrl);
                    mImageLoaderListener.onError(error);
                }

                @Override
                protected void onProgressUpdate(Integer... values) {
                    mImageLoaderListener.onProgressChange(values[0]);
                }

                @Override
                protected Bitmap doInBackground(Void... params) {
                    Bitmap bitmap = null;
                    HttpURLConnection connection = null;
                    InputStream is = null;
                    ByteArrayOutputStream out = null;
                    try {
                        connection = (HttpURLConnection) new URL(imageUrl).openConnection();
                        if (displayProgress) {
                            connection.connect();
                            final int length = connection.getContentLength();
                            if (length <= 0) {
                                error = new ImageError("Invalid content length. The URL is probably not pointing to a file")
                                        .setErrorCode(ImageError.ERROR_INVALID_FILE);
                                this.cancel(true);
                            }
                            is = new BufferedInputStream(connection.getInputStream(), 8192);
                            out = new ByteArrayOutputStream();
                            byte bytes[] = new byte[8192];
                            int count;
                            long read = 0;
                            while ((count = is.read(bytes)) != -1) {
                                read += count;
                                out.write(bytes, 0, count);
                                publishProgress((int) ((read * 100) / length));
                            }
                            bitmap = BitmapFactory.decodeByteArray(out.toByteArray(), 0, out.size());
                        } else {
                            is = connection.getInputStream();
                            bitmap = BitmapFactory.decodeStream(is);
                        }
                    } catch (Throwable e) {
                        if (!this.isCancelled()) {
                            error = new ImageError(e).setErrorCode(ImageError.ERROR_GENERAL_EXCEPTION);
                            this.cancel(true);
                        }
                    } finally {
                        try {
                            if (connection != null)
                                connection.disconnect();
                            if (out != null) {
                                out.flush();
                                out.close();
                            }
                            if (is != null)
                                is.close();
                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    }
                    return bitmap;
                }

                @Override
                protected void onPostExecute(Bitmap result) {
                    if (result == null) {
                        Log.e(TAG, "factory returned a null result");
                        mImageLoaderListener.onError(new ImageError("downloaded file could not be decoded as bitmap")
                                .setErrorCode(ImageError.ERROR_DECODE_FAILED));
                    } else {
                        Log.d(TAG, "download complete, " + result.getByteCount() +
                                " bytes transferred");
                        mImageLoaderListener.onComplete(result);
                    }
                    mUrlsInProgress.remove(imageUrl);
                    System.gc();
                }
            }.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR);
        }

        public interface OnBitmapSaveListener {
            void onBitmapSaved();
            void onBitmapSaveError(ImageError error);
        }


        public static void writeToDisk(@NonNull final File imageFile, @NonNull final Bitmap image,
                                       @NonNull final OnBitmapSaveListener listener,
                                       @NonNull final Bitmap.CompressFormat format, boolean shouldOverwrite) {

            if (imageFile.isDirectory()) {
                listener.onBitmapSaveError(new ImageError("the specified path points to a directory, " +
                        "should be a file").setErrorCode(ImageError.ERROR_IS_DIRECTORY));
                return;
            }

            if (imageFile.exists()) {
                if (!shouldOverwrite) {
                    listener.onBitmapSaveError(new ImageError("file already exists, " +
                            "write operation cancelled").setErrorCode(ImageError.ERROR_FILE_EXISTS));
                    return;
                } else if (!imageFile.delete()) {
                    listener.onBitmapSaveError(new ImageError("could not delete existing file, " +
                            "most likely the write permission was denied")
                            .setErrorCode(ImageError.ERROR_PERMISSION_DENIED));
                    return;
                }
            }

            File parent = imageFile.getParentFile();
            if (!parent.exists() && !parent.mkdirs()) {
                listener.onBitmapSaveError(new ImageError("could not create parent directory")
                        .setErrorCode(ImageError.ERROR_PERMISSION_DENIED));
                return;
            }

            try {
                if (!imageFile.createNewFile()) {
                    listener.onBitmapSaveError(new ImageError("could not create file")
                            .setErrorCode(ImageError.ERROR_PERMISSION_DENIED));
                    return;
                }
            } catch (IOException e) {
                listener.onBitmapSaveError(new ImageError(e).setErrorCode(ImageError.ERROR_GENERAL_EXCEPTION));
                return;
            }

            new AsyncTask<Void, Void, Void>() {

                private ImageError error;

                @Override
                protected Void doInBackground(Void... params) {
                    FileOutputStream fos = null;
                    try {
                        fos = new FileOutputStream(imageFile);
                        image.compress(format, 100, fos);
                    } catch (IOException e) {
                        error = new ImageError(e).setErrorCode(ImageError.ERROR_GENERAL_EXCEPTION);
                        this.cancel(true);
                    } finally {
                        if (fos != null) {
                            try {
                                fos.flush();
                                fos.close();
                            } catch (IOException e) {
                                e.printStackTrace();
                            }
                        }
                    }
                    return null;
                }

                @Override
                protected void onCancelled() {
                    listener.onBitmapSaveError(error);
                }

                @Override
                protected void onPostExecute(Void result) {
                    listener.onBitmapSaved();
                }
            }.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR);
        }

        public static Bitmap readFromDisk(@NonNull File imageFile) {
            if (!imageFile.exists() || imageFile.isDirectory()) return null;
            return BitmapFactory.decodeFile(imageFile.getAbsolutePath());
        }

        public interface OnImageReadListener {
            void onImageRead(Bitmap bitmap);
            void onReadFailed();
        }

        public static void readFromDiskAsync(@NonNull File imageFile, @NonNull final OnImageReadListener listener) {
            new AsyncTask<String, Void, Bitmap>() {
                @Override
                protected Bitmap doInBackground(String... params) {
                    return BitmapFactory.decodeFile(params[0]);
                }

                @Override
                protected void onPostExecute(Bitmap bitmap) {
                    if (bitmap != null)
                        listener.onImageRead(bitmap);
                    else
                        listener.onReadFailed();
                }
            }.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR, imageFile.getAbsolutePath());
        }

        public static final class ImageError extends Throwable {

            private int errorCode;
            public static final int ERROR_GENERAL_EXCEPTION = -1;
            public static final int ERROR_INVALID_FILE = 0;
            public static final int ERROR_DECODE_FAILED = 1;
            public static final int ERROR_FILE_EXISTS = 2;
            public static final int ERROR_PERMISSION_DENIED = 3;
            public static final int ERROR_IS_DIRECTORY = 4;


            public ImageError(@NonNull String message) {
                super(message);
            }

            public ImageError(@NonNull Throwable error) {
                super(error.getMessage(), error.getCause());
                this.setStackTrace(error.getStackTrace());
            }

            public ImageError setErrorCode(int code) {
                this.errorCode = code;
                return this;
            }

            public int getErrorCode() {
                return errorCode;
            }
        }
    }*/

    /* private void setupSharedPreferences() {
        SharedPreferences sharedPreferences = PreferenceManager.getDefaultSharedPreferences(this);
        sharedPreferences.registerOnSharedPreferenceChangeListener((SharedPreferences.OnSharedPreferenceChangeListener) this);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();
        if (id == R.id.action_settings) {
            Intent intent = new Intent(MainActivity.this, SettingsActivity.class);
            startActivity(intent);
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    @Override
    public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key) {

        if (key.equals("display_text")) {
            setTextVisible(sharedPreferences.getBoolean("display_text",true));
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        androidx.preference.PreferenceManager.getDefaultSharedPreferences(this)
                .unregisterOnSharedPreferenceChangeListener((SharedPreferences.OnSharedPreferenceChangeListener) this);
    } */

    public static class SettingsFragment extends PreferenceFragmentCompat {
        @Override
        public void onCreatePreferences(Bundle savedInstanceState, String rootKey) {
            setPreferencesFromResource(R.xml.root_preferences, rootKey);

            //bindPreferenceSummaryToValue(findPreference("switch"));
            //bindPreferenceSummaryToValue(findPreference("list"));

            //TODO this is what you need to keep track of the value. You cant get the state, but you can get a listener that will do sth when the value changes
            Preference i = findPreference("theme");
            i.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {
                @Override
                public boolean onPreferenceChange(Preference preference, Object newValue) {
                    //TODO: Do sth when it changed. You can keep a boolean value that changes with this method, and take the state from it.
                    return false;
                }
            });
        }
    }




}