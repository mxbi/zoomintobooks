package com.uniform.zoomintobooks;

import androidx.appcompat.app.AppCompatActivity;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.drawable.Icon;
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

        MoreButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                onMoreButtonClicked();
            }
        });


    }

    private void onMoreButtonClicked() {
        setVisibility(MoreButtonOpen);
        setAnimations(MoreButtonOpen);
        MoreButtonOpen=!MoreButtonOpen;
    }

    private void setAnimations(Boolean moreButtonOpen) {
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

    private void setVisibility(Boolean moreButtonOpen) {
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

    private int getViewWidth() {
        DisplayMetrics displayMetrics = new DisplayMetrics();
        getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
        return displayMetrics.widthPixels;
    }

}