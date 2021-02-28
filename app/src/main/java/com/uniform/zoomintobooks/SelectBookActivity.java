package com.uniform.zoomintobooks;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ListView;
import android.widget.SearchView;

import com.google.android.material.floatingactionbutton.FloatingActionButton;

import java.util.ArrayList;

public class SelectBookActivity extends AppCompatActivity implements SearchView.OnQueryTextListener {
    Boolean MoreButtonOpen = false;
    ListView list;
    ListViewAdapter adapter;
    SearchView editsearch;
    ArrayList<String> arraylist = new ArrayList<>();


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
        adapter = new ListViewAdapter(this, arraylist);
        // Binds the Adapter to the ListView
        list.setAdapter(adapter);

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
        //get books that match string into arraylist
        arraylist = null;
        adapter.notifyDataSetChanged();
        return false;
    }
}