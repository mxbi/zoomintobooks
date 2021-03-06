package com.uniform.zoomintobooks.common.helpers;

import androidx.annotation.Nullable;

import java.util.HashMap;

public class UrlToUri<S,T> extends HashMap<S,T> {
    int filename=90;
    @Nullable
    @Override
    public T get(@Nullable Object key) {
        return super.get(key);
    }

    @Nullable
    @Override
    public T put(S key, T value) {
        T t = super.put(key, value);
        return t;
    }

    public String newUri(String extension){
        this.filename++;
        String s = String.valueOf(filename).concat(extension);
        return s;
    }

}
