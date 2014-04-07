package com.simons.bluetoothtracker.activities;

        import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.TableRow.LayoutParams;
import android.widget.TextView;

import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.JsonHttpResponseHandler;
import com.loopj.android.http.PersistentCookieStore;
import com.loopj.android.http.TextHttpResponseHandler;
        import com.simons.bluetoothtracker.BluetoothTrackerApplication;
        import com.simons.bluetoothtracker.R;
        import com.simons.bluetoothtracker.settings.UserSettings;

        import org.json.JSONArray;
import org.json.JSONObject;

//import com.bierturf.app.TurfList.TurfBlad;

public class ListActivity extends Activity {

    private final Context context = this;

    private static final String KEY_MESSAGE = "KEY_MESSAGE";

    private static final String BASE_URL = "http://bierturf.ipscms.com/apicall.php?";
    private static AsyncHttpClient client = new AsyncHttpClient();
    private PersistentCookieStore cookieStore;

    private BluetoothTrackerApplication application;

    private UserSettings userSettings;

    //private TurfList turflist;
    //private List<TurfBlad> turflists = new ArrayList<TurfBlad>();


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_list);

        //bPlusBier = (Button)findViewById(R.id.buttonPlusBier);
        //bMinBier = (Button)findViewById(R.id.buttonMinBier);
        //tInfo = (TextView)findViewById(R.id.textResponse);

        cookieStore = new PersistentCookieStore(getApplicationContext());
        application = (BluetoothTrackerApplication) getApplication();
        userSettings = application.loadUserSettings();

        //bPlusBier.setOnClickListener(plusBier);
        //bMinBier.setOnClickListener(minBier);


        if (userSettings != null) {
            client.setCookieStore(cookieStore);

            //credentials = email + "," + pass;

            login(userSettings.email, userSettings.pass);
        } else {
            Intent intent = new Intent(context, LoginFragment.class);
            intent.putExtra(KEY_MESSAGE, "E001");

            startActivity(intent);
        }
    }

    public void login(final String email, final String pass) {
        client.get(BASE_URL + "function=login&input=" + email + "," + pass, null, new TextHttpResponseHandler() {
            @Override
            public void onSuccess(int StatusCode, org.apache.http.Header[] headers, String response) {
                ((TextView) findViewById(R.id.debugText)).setText(response);
                if (response.contains("true")) {
                    getDeviceList(email, pass);
                }
                else {
                    Intent intent = new Intent(context, LoginFragment.class);
                    intent.putExtra(KEY_MESSAGE, "E002");

                    startActivity(intent);
                }
            }

            @Override
            public void onFailure(int StatusCode, org.apache.http.Header[] headers, String responseBody, Throwable error) {
                ((TextView) findViewById(R.id.debugText)).setText(error.getMessage());
            }
        });
    }

    public void getDeviceList(final String email, final String pass) {
        //turflist = new TurfList((Activity) context, client, cookieStore, email, pass);
        client.get(BASE_URL + "function=getlists", null, new JsonHttpResponseHandler() {
            @Override
            public void onSuccess(int StatusCode, org.apache.http.Header[] headers, JSONObject response) {
                //((TextView) findViewById(R.id.debugText)).setText(response);
                addDevices(response, email, pass);
                //(new Dialog("DashBoard", "Live kratten:/n" + response)).showDialog(getApplicationContext());
            }
        });
    }

    public void addDevices(JSONObject json, String email, String pass) {
        TextView debug = (TextView) findViewById(R.id.debugText);
        try {
            JSONArray names = json.names();
            for (int i = 0; i < names.length(); i++) {
                JSONObject j = json.getJSONObject(names.getString(i));
                String deviceID = Integer.toString(j.getInt("ID"));
                String title = j.getString("Title");
                addListButton(deviceID, title, email, pass);
                //turflists.add(new TurfBlad(id, title, email, pass));
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void addListButton(final String deviceID, String title, final String email, final String pass) {
        LinearLayout ll = (LinearLayout) findViewById(R.id.DeviceListLayout);

        Button b = new Button(context);
        LayoutParams lp = new LayoutParams(
                LayoutParams.MATCH_PARENT,
                200);
        b.setLayoutParams(lp);
        b.setText(title);
        b.setOnClickListener(new Button.OnClickListener() {

            @Override
            public void onClick(View v) {
                // TODO Auto-generated method stub
                /*Intent intent = new Intent(context, DashboardActivity.class);
                intent.putExtra(KEY_EMAIL, email);
                intent.putExtra(KEY_PASS, pass);
                intent.putExtra(KEY_LISTID, listId);

                //((TextView) findViewById(R.id.debugText)).setText(email + "," + pass + "," + listId);

                startActivity(intent);*/
            }
        });
        ll.addView(b);
    }
	/*public class TurfBlad {
		public final String listId;
		public final String title;
		public final String email;
		public final String pass;

		public Activity activity;

		public TurfBlad(String _listId, String _title, String _email, String _pass) {
			listId = _listId;
			title = _title;
			//activity = _activity;
			email = _email;
			pass = _pass;

			int w = ((ViewGroup) activity.findViewById(R.id.UpperLayer)).getWidth();
			int m = 10;
			w -= 12*m;

			LinearLayout ll = (LinearLayout) activity.findViewById(R.id.TurfListLayout);

			Button b = new Button(context);
			LayoutParams lp = new LayoutParams(
					LayoutParams.MATCH_PARENT,
					200);
			b.setLayoutParams(lp);
			b.setText(title);
			b.setOnClickListener(new Button.OnClickListener() {

				@Override
				public void onClick(View v) {
					// TODO Auto-generated method stub
					Intent intent = new Intent(context, DashboardActivity.class);
			    	intent.putExtra(KEY_EMAIL, email);
			    	intent.putExtra(KEY_PASS, pass);
			    	intent.putExtra(KEY_LISTID, listId);

			    	activity.startActivity(intent);
				}
			});
			ll.addView(b);


			client.get(BASE_URL + "nrfunctions=2&array=1&" +
					"function1=aantalInKrat&input1=1," + Integer.toString(id) + "&" +
					"function2=gedronken&input2=1," + Integer.toString(id) + ",0", null, new AsyncHttpResponseHandler() {
				@SuppressLint("DefaultLocale")
				@Override
				public void onSuccess(String response) {
					JSONArray jArr = null;
					String over = "";
					String gedronken = "";
					try {
						jArr = new JSONArray(response);

						over = jArr.getString(0);
						gedronken = jArr.getString(1);
					} catch (JSONException e) {
						debug(e.toString());
						e.printStackTrace();
					}

					int w = ((ViewGroup) activity.findViewById(R.id.UpperLayer)).getWidth();
					int m = 10;
					w -= 12*m;

					final ProgressBar pB = new ProgressBar(context, null, android.R.attr.progressBarStyleHorizontal);


					TableLayout table = ((TableLayout) activity.findViewById(R.id.kratTable));
					pB.setMax(24);

					// Create a TableRow and give it an ID
					TableRow tr = new TableRow(context);
					tr.setId(1000+id);
					LayoutParams lp = new LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT);
					//lp.height = 200;
					lp.setMargins(0,0,0,0);
					tr.setLayoutParams(lp);
					tr.setBackgroundColor(color.white);


					//Create add button
					//Button bPlus = new Button(context);
					View.OnClickListener onClickPlus = new View.OnClickListener() {
						@Override
						public void onClick(View v) {
							client.get(BASE_URL+ "nrfunctions=3&array=1&" +
									"function1=plusBiertje&input1=1,false," + Integer.toString(id) + "&" +
									"function2=aantalInKrat&input2=1," + Integer.toString(id) + "&" +
									"function3=gedronken&input3=1," + Integer.toString(id) + ",0", null, new AsyncHttpResponseHandler() {
								@Override
								public void onSuccess(String response) {
									JSONArray jArr = null;
									String o = "";
									String g = "";
									try {
										jArr = new JSONArray(response);

										o = jArr.getString(1);
										g = jArr.getString(2);
									} catch (JSONException e) {
										debug(e.toString());
										e.printStackTrace();
									}

									pB.setProgress(Integer.parseInt(o));
									//((TextView) activity.findViewById(2000+id)).setText("Over: " + o);
									//((TextView) activity.findViewById(3000+id)).setText("Gedronken: " + g);

								}
							});
						}
					};

					Drawable d = getImage(brand.replace(" ", "").toLowerCase());
					if (d != null) {
						ImageButton bPlus = new ImageButton(context);
						bPlus.setImageDrawable(d);
						bPlus.setScaleType(ScaleType.FIT_CENTER);
						bPlus.setAdjustViewBounds(true);
						lp = new LayoutParams(
								(int)(0.6 * w) + 100,
								200);
						//lp.width = ;
						//lp.height =
						bPlus.setLayoutParams(lp);
						bPlus.setOnClickListener(onClickPlus);
						//bPlus.requestLayout();
						tr.addView(bPlus);
					} else {
						Button bPlus = new Button(context);
						bPlus.setText(brand);
						bPlus.setBackgroundResource(activity.getResources().getIdentifier("@android:drawable/btn_default_holo_light", "drawable", activity.getPackageName()));
						//bPlus.getBackground().setColorFilter(0xFFD6D6D6, PorterDuff.Mode.SRC);
						lp = new LayoutParams(
								(int)(0.6 * w) + 100,
								200);
						bPlus.setLayoutParams(lp);
						bPlus.setTextColor(Color.BLACK);
						bPlus.setOnClickListener(onClickPlus);
						tr.addView(bPlus);
					}

					LinearLayout ll = new LinearLayout(context);
					ll.setLayoutParams(new LayoutParams(
							(int)(0.4 * w),
							LayoutParams.MATCH_PARENT));
					ll.setOrientation(LinearLayout.VERTICAL);


					//Create min button
					Button bMin = new Button(context);
					bMin.setText("-1 bier");
					lp = new LayoutParams(
							LayoutParams.WRAP_CONTENT,
							LayoutParams.MATCH_PARENT);
					lp.width = (int)(0.4 * w);
					lp.height = 100;
					bMin.setLayoutParams(lp);
					bMin.setTextColor(Color.BLACK);
					bMin.setBackgroundResource(activity.getResources().getIdentifier("@android:drawable/btn_default_holo_light", "drawable", activity.getPackageName()));
					bMin.setWidth((int)(0.1 * w));
					bMin.setHeight(100);
					bMin.setOnClickListener(new View.OnClickListener() {
						@Override
						public void onClick(View v) {
							client.get(BASE_URL+ "nrfunctions=3&array=1&" +
									"function1=minBiertje&input1=1,false," + Integer.toString(id) + "&" +
									"function2=aantalInKrat&input2=1," + Integer.toString(id) + "&" +
									"function3=gedronken&input3=1," + Integer.toString(id) + ",0", null, new AsyncHttpResponseHandler() {
								@Override
								public void onSuccess(String response) {
									JSONArray jArr = null;
									String o = "";
									String g = "";
									try {
										jArr = new JSONArray(response);

										o = jArr.getString(1);
										g = jArr.getString(2);
									} catch (JSONException e) {
										debug(e.toString());
										e.printStackTrace();
									}

									pB.setProgress(Integer.parseInt(o));
									//((TextView) activity.findViewById(2000+id)).setText("Over: " + o);
									//((TextView) activity.findViewById(3000+id)).setText("Gedronken: " + g);
								}
							});
						}
					});
					ll.addView(bMin);

					//Create number add button
					Button bNum = new Button(context);
					bNum.setText("# bier");
					lp = new LayoutParams(
							LayoutParams.WRAP_CONTENT,
							LayoutParams.MATCH_PARENT);
					lp.width = (int)(0.4 * w);
					lp.height = 100;
					bNum.setLayoutParams(lp);
					bNum.setTextColor(Color.BLACK);
					bNum.setBackgroundResource(activity.getResources().getIdentifier("@android:drawable/btn_default_holo_light", "drawable", activity.getPackageName()));
					bNum.setOnClickListener(new View.OnClickListener() {
						@Override
						public void onClick(View v) {

							// get prompts.xml view
							LayoutInflater li = LayoutInflater.from(context);
							View promptsView = li.inflate(R.layout.dialog_prompt, null);

							AlertDialog.Builder alertDialogBuilder = new AlertDialog.Builder(context);

							// set prompts.xml to alertdialog builder
							alertDialogBuilder.setView(promptsView);

							final EditText userInput = (EditText) promptsView.findViewById(R.id.editTextDialogUserInput);

							// set dialog message
							alertDialogBuilder
							.setCancelable(false)
							.setPositiveButton("OK",
									new OnClickListener() {

								@Override
								public void onClick(DialogInterface dialog, int which) {
									// TODO Auto-generated method stub
									String num = userInput.getText().toString();
									client.get(BASE_URL+ "nrfunctions=3&array=1&" +
											"function1=setAantal&input1=1," + Integer.toString(id) + ",false," + num + "&" +
											"function2=aantalInKrat&input2=1," + Integer.toString(id) + "&" +
											"function3=gedronken&input3=1," + Integer.toString(id) + ",0", null, new AsyncHttpResponseHandler() {
										@Override
										public void onSuccess(String response) {
											JSONArray jArr = null;
											String o = "";
											String g = "";
											try {
												 jArr = new JSONArray(response);

												 o = jArr.getString(1);
												 g = jArr.getString(2);
											} catch (JSONException e) {
												debug(e.toString());
												e.printStackTrace();
											}

											pB.setProgress(Integer.parseInt(o));
											//((TextView) activity.findViewById(2000+id)).setText("Over: " + o);
											//((TextView) activity.findViewById(3000+id)).setText("Gedronken: " + g);
										}
									});
								}
							})
							.setNegativeButton("cancel",
									new OnClickListener() {

								@Override
								public void onClick(DialogInterface dialog, int which) {
									// TODO Auto-generated method stub
									dialog.cancel();
								}
							});

							// create alert dialog
							AlertDialog alertDialog = alertDialogBuilder.create();

							// show it
							alertDialog.show();
						}
					});
					ll.addView(bNum);

					tr.addView(ll);

					// Add the TableRow to the TableLayout
					table.addView(tr, new LinearLayout.LayoutParams(
							LayoutParams.MATCH_PARENT,
							200));

					// Create a TableRow and give it an ID
					tr = new TableRow(context);
					tr.setId(4000+id);
					lp = new LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT);
					//lp.height = 200;
					lp.span = 2;
					lp.setMargins(0, 0, 0, 2);
					tr.setLayoutParams(lp);
					tr.setBackgroundColor(color.white);

					lp = new LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT);
					lp.span = 2;
					pB.setLayoutParams(lp);
					pB.setProgress(Integer.parseInt(over));
					tr.addView(pB);

					table.addView(tr, new LinearLayout.LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT));

					// Create a TextView to house the name of the province
					TextView tOver = new TextView(context);
					//labelTV.setId(200+id);
					tOver.setId(2000+id);
					tOver.setText("Over: " + over);
					tOver.setTextColor(Color.BLACK);
					tOver.setLayoutParams(new LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT));
					tr.addView(tOver);

					// Create a TextView to house the name of the province
					TextView tGedronken = new TextView(context);
					//labelTV.setId(200+id);
					tGedronken.setId(3000+id);
					tGedronken.setText("Gedronken: " + gedronken);
					tGedronken.setTextColor(Color.BLACK);
					tGedronken.setLayoutParams(new LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT));
					tr.addView(tGedronken);

					table.addView(tr, new LinearLayout.LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT));

					// Create a TableRow and give it an ID
					tr = new TableRow(context);
					lp = new LayoutParams(
							LayoutParams.MATCH_PARENT,
							2);
					//lp.height = 200;
					lp.setMargins(0, 0, 0, 2);
					tr.setLayoutParams(lp);
					tr.setBackgroundColor(Color.GRAY);

					// Create a TextView to house the name of the province
					TextView tLine = new TextView(context);
					//labelTV.setId(200+id);
					tLine.setText("");
					tLine.setTextColor(Color.BLACK);
					tLine.setBackgroundColor(Color.GRAY);
					tLine.setLayoutParams(new LayoutParams(
							LayoutParams.MATCH_PARENT,
							2));
					tr.addView(tLine);

					table.addView(tr, new LinearLayout.LayoutParams(
							LayoutParams.MATCH_PARENT,
							LayoutParams.WRAP_CONTENT));

					tr = new TableRow(context);
					tr.setLayoutParams(new LayoutParams(
							LayoutParams.MATCH_PARENT,
							5));

					// Create a TableRow and give it an ID
					tr = new TableRow(context);
					tr.setId(5000+id);
					lp = new LayoutParams(
							LayoutParams.MATCH_PARENT,
							2);
					//lp.height = 200;
					tr.setLayoutParams(lp);
					tr.setBackgroundColor(color.black);

					// Add the TableRow to the TableLayout
					table.addView(tr, new LinearLayout.LayoutParams(
							LayoutParams.MATCH_PARENT,
							2));
				}
			});
		}
	}*/





}
