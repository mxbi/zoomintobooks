package com.uniform.zoomintobooks;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import com.google.android.material.floatingactionbutton.FloatingActionButton;

public class BookActivity extends AppCompatActivity {
    Boolean MoreButtonOpen = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_book);


        FloatingActionButton ReturnButton = findViewById(R.id.Return);
        ReturnButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(view.getContext(), (Class) getIntent().getExtras().get("CurrentAct"));
            startActivity(startIntent);
        });

        ReturnButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

            }
        });
    }



}