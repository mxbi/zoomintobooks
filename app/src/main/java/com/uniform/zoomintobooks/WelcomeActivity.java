package com.uniform.zoomintobooks;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.graphics.drawable.Icon;
import android.icu.text.IDNA;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.view.Display;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.Toast;

import com.uniform.zoomintobooks.R;
import com.google.android.material.floatingactionbutton.FloatingActionButton;


public class WelcomeActivity extends AppCompatActivity {
    Boolean MoreButtonOpen = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        setContentView(R.layout.activity_welcome);

        FloatingActionButton MoreButton = findViewById(R.id.MoreButton);
        MoreButton.setOnClickListener(v -> onMoreButtonClicked());

        Button ScanButton = findViewById(R.id.ScanButton);
        Button SearchButton = findViewById(R.id.SearchButton);
        ScanButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(),BarcodeScanActivity.class);
            startActivity(startIntent);
        });
        SearchButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(),SelectBookActivity.class);
            startActivity(startIntent);
        });

        setMenuButtons();
    }

    private void setMenuButtons() {
        FloatingActionButton InfoButton = findViewById(R.id.InfoButton);
        InfoButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), InfoActivity.class);
            startIntent.putExtra("CurrentAct",WelcomeActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton BookButton = findViewById(R.id.BookButton);
        BookButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), BookActivity.class);
            startIntent.putExtra("CurrentAct",WelcomeActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton ContactButton = findViewById(R.id.ContactButton);
        ContactButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), ContactActivity.class);
            startIntent.putExtra("CurrentAct",WelcomeActivity.class);
            startActivity(startIntent);
        });

        FloatingActionButton SettingButton = findViewById(R.id.SettingButton);
        SettingButton.setOnClickListener(view -> {
            Intent startIntent = new Intent(getApplicationContext(), SettingsActivity.class);
            startIntent.putExtra("CurrentAct",WelcomeActivity.class);
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
        if (!moreButtonOpen) {
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
        if (!moreButtonOpen) {
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


}