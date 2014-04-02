package com.devriesdev.utils;

import android.content.Context;
import android.widget.TextView;

import com.devriesdev.devicemanager.R;

/**
 * Created by danie_000 on 4/2/2014.
 */
public class Dialog {
    private final android.app.Dialog dialog;
    private final TextView textView;

    public Dialog(Context context) {
        dialog = new android.app.Dialog(context);
        dialog.setContentView(R.layout.popup);
        textView = (TextView) dialog.findViewById(R.id.popupText);
    }

    public void show(String text) {
        textView.setText(text);
        dialog.show();
    }

}
