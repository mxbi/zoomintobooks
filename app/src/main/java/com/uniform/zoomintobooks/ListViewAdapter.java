package com.uniform.zoomintobooks;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;

import com.uniform.zoomintobooks.R;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

public class ListViewAdapter extends BaseAdapter {

    // Declare Variables

    Context mContext;
    LayoutInflater inflater;
    ArrayList<String> Titlelist = new ArrayList<>();
    ArrayList<String> ISBNlist = new ArrayList<>();

    public ListViewAdapter(Context context, ArrayList<String> Titlelist,ArrayList<String> ISBNlist) {
        mContext = context;
        this.Titlelist = Titlelist;
        this.ISBNlist = ISBNlist;
        inflater = LayoutInflater.from(mContext);
    }

    public void changeData(ArrayList<String> titlelist,ArrayList<String> ISBNlist) {
        this.Titlelist = titlelist;
        this.ISBNlist = ISBNlist;
    }


    public class ViewHolder {
        TextView name;
    }

    @Override
    public int getCount() {
        return Titlelist.size();
    }

    @Override
    public String getItem(int position) {
        return ISBNlist.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    public View getView(final int position, View view, ViewGroup parent) {
        final ViewHolder holder;
        if (view == null) {
            holder = new ViewHolder();
            view = inflater.inflate(R.layout.list_view_items, null);
            // Locate the TextViews in listview_item.xml
            holder.name = view.findViewById(R.id.name);
            view.setTag(holder);
        } else {
            holder = (ViewHolder) view.getTag();
        }
        // Set the results into TextViews
        holder.name.setText(Titlelist.get(position));
        return view;
    }



}