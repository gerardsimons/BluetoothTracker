package com.devriesdev.devicemanager;

//import org.json.JSONArray;

//import com.loopj.android.http.JsonHttpResponseHandler;

import android.app.Fragment;
import android.app.FragmentTransaction;
import android.content.Context;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.os.Bundle;
import android.support.v4.app.LoaderManager;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.CompoundButton.OnCheckedChangeListener;
import android.widget.EditText;
import android.widget.ListView;

import com.devriesdev.connection.Connection;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
//import android.widget.TextView;

public class ListFragment extends Fragment {

    private Context context;

    private static Connection connection;
    private View v;

    private ListView listView;

    private static final String TAG = "ListFragment";

    public ListFragment() {
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        v = inflater.inflate(R.layout.fragment_list, container, false);
        return v;
    }

    @Override
    public void onActivityCreated(Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        //setContentView(R.layout.activity_login);

        listView = (ListView) getView().findViewById(R.id.list_view);

        context = getActivity();
        connection = ((MainActivity) context).getConnection();

        connection.authIsLoggedIn(new Connection.OwnHandler() {
            @Override
            public void handle(Object o) {
                if (o instanceof String) {
                    if (o.equals("1")) {
                        getList();
                    } else {
                        getFragmentManager().popBackStackImmediate();
                    }
                } else {
                    Log.v(TAG, "Something went wrong: authIsLoggedIn did not return String");
                }
            }
        });
    }

    private void getList() {
        connection.doRequest(new Connection.OwnHandler() {
            @Override
            public void handle(Object o) {
                if (o instanceof JSONArray) {
                    JSONArray jsonArray = (JSONArray)o;
                    Log.v(TAG, "Got labels!: \n" + jsonArray.toString());

                    final ArrayList<String> list = new ArrayList<String>();
                    for (int i = 0; i<jsonArray.length(); i++) {
                        try {
                            list.add(jsonArray.getJSONObject(i).getString("mac"));
                        } catch (JSONException e) {};
                    }

                    final StableArrayAdapter adapter = new StableArrayAdapter(context,android.R.layout.simple_list_item_1, list);

                    listView.setAdapter(adapter);

                    listView.setOnItemClickListener(new AdapterView.OnItemClickListener() {

                        @Override
                        public void onItemClick(AdapterView<?> parent, final View view, int position, long id) {
                            final String item = (String) parent.getItemAtPosition(position);
                            //TODO Do something with the pressed item
                        }
                    });
                } else {
                    Log.v(TAG, "Got some shit... \n" + (String)o);
                }
            }
        }, "label.getlabels");
    }

    private class StableArrayAdapter extends ArrayAdapter<String> {

        HashMap<String, Integer> mIdMap = new HashMap<String, Integer>();

        public StableArrayAdapter(Context context, int textViewResourceId,
                                  List<String> objects) {
            super(context, textViewResourceId, objects);
            for (int i = 0; i < objects.size(); ++i) {
                mIdMap.put(objects.get(i), i);
            }
        }

        @Override
        public long getItemId(int position) {
            String item = getItem(position);
            return mIdMap.get(item);
        }

        @Override
        public boolean hasStableIds() {
            return true;
        }

    }

    @Override
    public void onPause() {
        super.onPause();
    }

    @Override
    public void onResume() {
        super.onResume();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        connection.doRequest(new Connection.OwnHandler() {
            @Override
            public void handle(Object o) {
                connection.authLogout();
            }
        }, "status.status");
    }
}
