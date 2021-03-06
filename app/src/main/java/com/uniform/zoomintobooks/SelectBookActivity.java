package com.uniform.zoomintobooks;

import androidx.appcompat.app.AppCompatActivity;

import android.app.Activity;
import android.content.Intent;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.ArrayMap;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.SearchView;
import android.widget.TextView;

import com.google.android.material.floatingactionbutton.FloatingActionButton;
import com.google.zxing.integration.android.IntentResult;
import com.uniform.zoomintobooks.common.helpers.BookInfo;
import com.uniform.zoomintobooks.common.helpers.ZoomUtils;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Set;

public class SelectBookActivity extends AppCompatActivity implements SearchView.OnQueryTextListener {
    Boolean MoreButtonOpen = false;
    ListView list;
    ListViewAdapter adapter;
    SearchView editsearch;
    ArrayList<String> Titlelist = new ArrayList<>();
    ArrayList<String> ISBNlist = new ArrayList<>();


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_select_book);

        FloatingActionButton MoreButton = findViewById(R.id.MoreButton);
        MoreButton.setOnClickListener(v -> onMoreButtonClicked());

        FloatingActionButton ReturnButton = findViewById(R.id.Return);
        ReturnButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(),WelcomeActivity.class);
            startActivity(startIntent);
        });

        setMenuButtons();

        // Locate the ListView in listview_main.xml
        list =  findViewById(R.id.listview);


        // Pass results to ListViewAdapter Class
        adapter = new ListViewAdapter(this, Titlelist,ISBNlist);
        // Binds the Adapter to the ListView
        list.setAdapter(adapter);

        new BookListTask().execute("sdyatiegrcbaruioenawegcfyuia");
        list.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                String item = adapter.getItem(position);
                new BookScanTask().execute(item);
            }
        });

        // Locate the EditText in listview_main.xml
        editsearch = findViewById(R.id.SearchForBookView);
        editsearch.setOnQueryTextListener(this);
    }

    private void setMenuButtons() {
        FloatingActionButton InfoButton = findViewById(R.id.InfoButton);
        InfoButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), InfoActivity.class);
            startIntent.putExtra("CurrentAct",SelectBookActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton BookButton = findViewById(R.id.BookButton);
        BookButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), BookActivity.class);
            startIntent.putExtra("CurrentAct",SelectBookActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton ContactButton = findViewById(R.id.ContactButton);
        ContactButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), ContactActivity.class);
            startIntent.putExtra("CurrentAct",SelectBookActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton SettingButton = findViewById(R.id.SettingButton);
        SettingButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), SettingsActivity.class);
            startIntent.putExtra("CurrentAct",SelectBookActivity.class);
            startActivity(startIntent);
        });
    }
    private void onMoreButtonClicked() {
        setVisibilityOfMenu(MoreButtonOpen);
        setAnimationsOfMenu(MoreButtonOpen);
        MoreButtonOpen=!MoreButtonOpen;
    }

    private void setAnimationsOfMenu(Boolean moreButtonOpen) {
        Animation rotateOpen = AnimationUtils.loadAnimation(this, R.anim.rotate_open_anim);
        Animation rotateClose = AnimationUtils.loadAnimation(this, R.anim.rotate_close_anim);
        Animation fromBottom = AnimationUtils.loadAnimation(this, R.anim.from_bottom_anim);
        Animation toBottom = AnimationUtils.loadAnimation(this, R.anim.to_bottom_anim);
        FloatingActionButton MoreButton = findViewById(R.id.MoreButton);
        FloatingActionButton InfoButton = findViewById(R.id.InfoButton);
        FloatingActionButton ContactButton = findViewById(R.id.ContactButton);
        FloatingActionButton BookButton = findViewById(R.id.BookButton);
        FloatingActionButton SettingButton = findViewById(R.id.SettingButton);
        if(!moreButtonOpen){
            MoreButton.setAnimation(rotateOpen);
            InfoButton.setAnimation(fromBottom);
            ContactButton.setAnimation(fromBottom);
            BookButton.setAnimation(fromBottom);
            SettingButton.setAnimation(fromBottom);
        } else {
            MoreButton.setAnimation(rotateClose);
            InfoButton.setAnimation(toBottom);
            ContactButton.setAnimation(toBottom);
            BookButton.setAnimation(toBottom);
            SettingButton.setAnimation(toBottom);
        }
    }

    private void setVisibilityOfMenu(Boolean moreButtonOpen) {
        FloatingActionButton InfoButton = findViewById(R.id.InfoButton);
        FloatingActionButton ContactButton = findViewById(R.id.ContactButton);
        FloatingActionButton BookButton = findViewById(R.id.BookButton);
        FloatingActionButton SettingButton = findViewById(R.id.SettingButton);
        if(!moreButtonOpen){
            InfoButton.setVisibility(View.VISIBLE);
            ContactButton.setVisibility(View.VISIBLE);
            BookButton.setVisibility(View.VISIBLE);
            SettingButton.setVisibility(View.VISIBLE);
        } else {
            InfoButton.setVisibility(View.INVISIBLE);
            ContactButton.setVisibility(View.INVISIBLE);
            BookButton.setVisibility(View.INVISIBLE);
            SettingButton.setVisibility(View.INVISIBLE);
        }
    }


    @Override
    public boolean onQueryTextSubmit(String query) {
        return false;
    }

    @Override
    public boolean onQueryTextChange(String newText) {

        new BookListTask().execute(newText);
        return false;
    }

    private class BookListTask extends AsyncTask<String, Void, ArrayMap<String, String>> {



        @Override
        protected ArrayMap<String, String> doInBackground(String... strings) {
            String string;
            if(strings[0].equals("")){
                string = "asdfghqwertyukl xdfhjkl";
            } else{
                string = strings[0];
            }
            String url = "https://api.uniform.ml/titles/"+string;
            ArrayMap<String, String> booklist = new ArrayMap<String, String>();
            try {
                booklist = ZoomUtils.parseJSONlist(url);
            } catch (IOException e) {
                // TODO: Handle this more gracefully
                e.printStackTrace();
            }

            return booklist;
        }

        protected void onPostExecute( ArrayMap<String, String> booklist) {

            //get books that match string into arraylist

            Titlelist = new ArrayList<>();
            ISBNlist = new ArrayList<>();
            Set<String> set = booklist.keySet();
            Iterator<String> itset = set.iterator();
            while(itset.hasNext()){
                String key = itset.next();
                Titlelist.add(booklist.get(key));
                ISBNlist.add(key);
            }

            adapter.changeData(Titlelist,ISBNlist);

            adapter.notifyDataSetChanged();

//
        }
    }
    private class BookScanTask extends AsyncTask<String, Void, BookInfo> {
        @Override
        protected BookInfo doInBackground(String... Strings) {
            String intentResult = Strings[0];
            return getBookInfo(intentResult);
        }

        protected void onPostExecute(BookInfo bookInfo) {
            Intent startIntent = new Intent(getApplicationContext(),AugmentedImageActivity.class);
            bookInfo.addAllToIntent(startIntent);
            startActivity(startIntent);

        }
    }

    private BookInfo getBookInfo(String contents) {
        String url = "https://api.uniform.ml/books/"+contents;
        BookInfo book = null;
        try {
            book = ZoomUtils.parseJSON(url);
        } catch (IOException e) {
            // TODO: Handle this more gracefully
            e.printStackTrace();
        }

        return book;
    }
}