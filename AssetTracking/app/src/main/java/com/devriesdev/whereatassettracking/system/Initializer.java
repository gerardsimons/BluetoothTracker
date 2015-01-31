package com.devriesdev.whereatassettracking.system;

import android.os.AsyncTask;

import com.devriesdev.whereatassettracking.utils.Utils;

/**
 * Created by danie_000 on 6/15/2014.
 */
public class Initializer extends AsyncTask<Utils.Executable, Void, Boolean> {
    private Utils.Executable executable;

    protected Boolean doInBackground(Utils.Executable... params) {
        executable = params[0];
        executable.execute();
        return true;
    }

    protected void onPostExecute(Boolean result) {
        executable.postExecute();
    }

    private Utils.InitiatedListener initiatedListener;
    private boolean isDone = false;

    public void addListener(Utils.InitiatedListener listener) {
        initiatedListener = listener;
    }

    public void isInitiated() {
        if (initiatedListener != null) {
            initiatedListener.onInitiated();
        }
    }

    public void done() {
        isDone = true;
    }

    public boolean isDone() {
        return isDone;
    }
}
