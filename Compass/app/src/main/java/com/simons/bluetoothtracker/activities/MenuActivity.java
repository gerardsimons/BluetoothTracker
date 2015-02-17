package com.simons.bluetoothtracker.activities;

import android.animation.Animator;
import android.animation.AnimatorSet;
import android.animation.ObjectAnimator;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.MotionEvent;
import android.view.View;
import android.view.animation.AccelerateDecelerateInterpolator;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.Toast;

import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.models.ProductType;

import java.util.ArrayList;
import java.util.List;

public class MenuActivity extends Activity {

    private static final String TAG = "MenuActivity";
    public static final String PRODUCT_TYPE_KEY = "productType";

    private boolean relayoutMenu = true;

    private int buttonSize;
    private int distanceToCenter;

    private ImageView logoView;
    private float logoCenterX, logoCenterY;

    private List<LinearLayout> rotatingButtons;
    //The button that is at the hotspot (i.e. currently selected icon)
    private Button hotspotButton;

    private RelativeLayout rootView;

    private float hotSpotAngle = 90F;

    private List<Animator> runningAnimations;

    //The logo rotation animation should only take place one, this boolean takes care of that
    private boolean introAnimationsDone = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.menu_activity);

        rotatingButtons = new ArrayList<LinearLayout>();
        runningAnimations = new ArrayList<Animator>();

        logoView = (ImageView) findViewById(R.id.logo);
        logoView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MenuActivity.this,DeviceScanActivity.class);
                startActivity(intent);
            }
        });

        rootView = (RelativeLayout) findViewById(R.id.menuRootView);
        //Find the buttons
        for (int i = 0; i < rootView.getChildCount(); i++) {
            View child = rootView.getChildAt(i);
            if (child instanceof LinearLayout) {
                LinearLayout layout = (LinearLayout) child;
                if(layout.getId() != R.id.buttonBar) { //Do not add the button bar
                    rotatingButtons.add(layout);
                }
            }
        }
        //Check when the layout has properly finished before doing anything UI related
        rootView.addOnLayoutChangeListener(new View.OnLayoutChangeListener() {
            @Override
            public void onLayoutChange(View view, int i, int i2, int i3, int i4, int i5, int i6, int i7, int i8) {
                if(!introAnimationsDone) {
                    doRotateLogoAnimation();
                }
                if (relayoutMenu) {
                    int minDim = Math.min(rootView.getWidth(),rootView.getHeight());
                    distanceToCenter = (int) (minDim / 2.5F);
                    buttonSize = (int) (minDim / 5F);
                    populateMenu(rotatingButtons);
                }
            }
        });

        rootView.setOnTouchListener(new View.OnTouchListener() {
            //Origin coordinates
            private float touchDownX,touchDownY;

            //Direction of drag; either up or down, up being positive, down being negative
            private int direction = -1;

            //last coordinates
            private float lastTouchY;
            private int moveDistanceThreshold = 5;

            //How often to update the position, 1 meaning every cycle
            private final int motionThrottle = 1;

            //Take track of the counts for the throttle purpose
            private int eventCount = 0;

            @Override
            public boolean onTouch(View view, MotionEvent motionEvent) {
                if (motionEvent.getAction() == MotionEvent.ACTION_DOWN) {
//                    Log.d(TAG, "motionEvent Down");
                    touchDownX = motionEvent.getX();
                    touchDownY = motionEvent.getY();
                    lastTouchY = touchDownY;
                } else if (motionEvent.getAction() == MotionEvent.ACTION_UP) {
//                    Log.d(TAG, "motionEvent Up");
                    LinearLayout button = buttonClosestToHotspot();
                    float distance = angularDistanceToHotSpot(button);
                    rotateButtonsAnimation(distance,750);
//                    float distance = (float)Math.sqrt(Math.pow(motionEvent.getX() - touchDownX,2) + Math.pow(motionEvent.getY() - touchDownY,2));
//                    setRotationButtons(distance / 10000F * 360F);
                } else if (motionEvent.getAction() == MotionEvent.ACTION_MOVE) {
//                    Log.d(TAG, "motionEvent Move");
                    eventCount++;
                    if (eventCount == motionThrottle) {
                        eventCount = 0;
                        if (motionEvent.getY() > lastTouchY + moveDistanceThreshold) {
                            lastTouchY = motionEvent.getY();
                            direction = -1;
                        } else if (motionEvent.getY() < lastTouchY - moveDistanceThreshold) {
                            lastTouchY = motionEvent.getY();
                            direction = 1;
                        } else {
//                            Log.d(TAG,"No direction.");
                            return true;
                        }
                        float distance = (float) Math.sqrt(Math.pow(motionEvent.getX() - touchDownX, 2) + Math.pow(motionEvent.getY() - touchDownY, 2));
                        rotateButtonsInstant(direction * distance / 100F);
                    }
                }
                return true;
            }
        });

        Button mapButton = (Button) findViewById(R.id.map_button);
        mapButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Toast.makeText(MenuActivity.this,"Not yet implemented.",Toast.LENGTH_SHORT).show();
            }
        });

        Button cloudButton = (Button) findViewById(R.id.map_button);
        cloudButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Toast.makeText(MenuActivity.this,"Not yet implemented.",Toast.LENGTH_SHORT).show();
            }
        });

        Button compassButton = (Button) findViewById(R.id.compass_button);
        compassButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if(hotspotButton != null) {
                   ProductType productType = null;
                   switch(hotspotButton.getId()) {
                       case R.id.keys_button:
                           productType = ProductType.KEYS;
                           break;
                       case R.id.umbrella_button:
                           productType = ProductType.UMBRELLA;
                           break;
                       case R.id.briefcase_button:
                           productType = ProductType.BRIEFCASE;
                           break;
                       case R.id.bike_button:
                           productType = ProductType.BIKE;
                           break;
                       case R.id.designer_bag_button:
                           productType = ProductType.BAG;
                           break;
                   }
                   if(productType != null) {
                       Intent intent = new Intent(MenuActivity.this,DeviceScanActivity.class);
                       intent.putExtra(PRODUCT_TYPE_KEY,productType);
                       startActivity(intent);
                   }
                }
            }
        });
    }

    private float angularDistanceToHotSpot(LinearLayout rotatingButtonWrapper) {
        return hotSpotAngle - rotatingButtonWrapper.getRotation();
    }

    private void populateMenu(List<LinearLayout> buttons) {

        logoCenterX = Math.round(rootView.getWidth() / 2F + rootView.getX());
        logoCenterY = Math.round(rootView.getHeight() / 2F + rootView.getY());

//        Log.d(TAG, "centerX = " + logoCenterX);
//        Log.d(TAG, "centerY = " + logoCenterY);

        float angleRadians = 0F;
        float angleDelta = (float) (Math.PI * 2 / buttons.size());

        relayoutMenu = false;

        logoView.setX(logoCenterX - logoView.getWidth() / 2);
        logoView.setY(logoCenterY - logoView.getHeight() / 2);

        for (int i = 0; i < buttons.size(); i++) {
            final LinearLayout buttonWrapper = rotatingButtons.get(i);

//            Log.d(TAG, "angle (in Radians) = " + angleRadians);

            //Set the position according to the circle around the logo
            buttonWrapper.setX(logoCenterX + distanceToCenter - buttonSize / 2);
            buttonWrapper.setY(logoCenterY - buttonSize / 2);

            //Set the pivot point to be the center of the logo
            buttonWrapper.setPivotX(logoCenterX - buttonWrapper.getX());
            buttonWrapper.setPivotY(logoCenterY - buttonWrapper.getY());

            float angleDegrees = (float) Math.toDegrees(angleRadians);

            buttonWrapper.setRotation(angleDegrees);
            final Button button = getButtonFromWrapper(buttonWrapper);
            button.setRotation(-angleDegrees);

            button.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    cancelAllAnimations();
                    hotspotButton = button;
                    rotateButtonsAnimation(angularDistanceToHotSpot(buttonWrapper),1500);
                }
            });

            angleRadians += angleDelta;
//            rootView.addView(button);
        }
//        rotateButtons(90F);
        relayoutMenu = true;
    }

    /**
     * Returns the button wrapper that is closest to the hotspot location.
     * @return the wrapper layout that contains the actual button.
     */
    private LinearLayout buttonClosestToHotspot() {
        float bestRotation = -1F;
        LinearLayout closestButton = null;
        for (LinearLayout button : rotatingButtons) {
            float distance = Math.abs(hotSpotAngle - button.getRotation());
            if (closestButton == null || distance < bestRotation) {
                bestRotation = distance;
                closestButton = button;
            }
        }
        return closestButton;
    }

    private Button getButtonFromWrapper(LinearLayout buttonWrapper) {
        return (Button) buttonWrapper.getChildAt(0);
    }

    private void rotateButtonsInstant(float angleIncrement) {
        for (final LinearLayout buttonWrapper : rotatingButtons) {
            buttonWrapper.setRotation(buttonWrapper.getRotation() + angleIncrement);
            Button button = getButtonFromWrapper(buttonWrapper);
            button.setRotation(button.getRotation() - angleIncrement);
        }
    }

    private void doRotateLogoAnimation() {
        ObjectAnimator rotation = ObjectAnimator.ofFloat(logoView,"rotation",360F);
//        ObjectAnimator rotationOvershoot = ObjectAnimator.ofFloat(logoView,"rotation",360F);

        AnimatorSet allAnimations = new AnimatorSet();
        allAnimations.playSequentially(rotation);

        rotation.setInterpolator(new AccelerateDecelerateInterpolator());

        allAnimations.setStartDelay(300);
        allAnimations.setDuration(1400);
        allAnimations.start();

        introAnimationsDone = true;
    }

    private void cancelAllAnimations() {
        for (Animator animator : runningAnimations) {
            animator.cancel();
        }
        runningAnimations.clear();
    }

    private void rotateButtonsAnimation(final float angle, int duration) {
        if (angle != 0F) {
            cancelAllAnimations();
            runningAnimations.clear();
            for (final LinearLayout buttonWrapper : rotatingButtons) {

                final Button button = getButtonFromWrapper(buttonWrapper);
                float fromAngle = buttonWrapper.getRotation();

                float toAngle = fromAngle + angle;
//                button.incrementRotationAroundLogo(angle);

//                Log.d(TAG, "ID = " + buttonWrapper.getId());
//                Log.d(TAG, "Angle = " + angle);
//                Log.d(TAG, "fromAngle = " + fromAngle);
//                Log.d(TAG, "toAngle = " + toAngle);

                //Epic somersaults, if this strikes your fancy
                int somerSaults = 0;

                ObjectAnimator wrapperRotation = ObjectAnimator.ofFloat(buttonWrapper, "rotation", toAngle);
                ObjectAnimator buttonRotation = ObjectAnimator.ofFloat(button, "rotation", 360F * somerSaults + button.getRotation() - angle);

                wrapperRotation.setDuration(duration);
                buttonRotation.setDuration(duration);

                wrapperRotation.addListener(new Animator.AnimatorListener() {
                    @Override
                    public void onAnimationStart(Animator animator) {

                    }

                    @Override
                    public void onAnimationEnd(Animator animator) {
//                        Log.d(TAG, "Button wrapper rotation = " + buttonWrapper.getRotation());
//                        Log.d(TAG, "Button rotation = " + button.getRotation());
                        buttonWrapper.setRotation((buttonWrapper.getRotation() + 360F) % 360F);
                        button.setRotation((button.getRotation() + 360F) % 360F);
                        runningAnimations.clear();
//                        Log.d(TAG, "Button wrapper normalized rotation = " + buttonWrapper.getRotation());
//                        Log.d(TAG, "Button normalized rotation = " + button.getRotation());
                    }

                    @Override
                    public void onAnimationCancel(Animator animator) {
                    }

                    @Override
                    public void onAnimationRepeat(Animator animator) {
                    }
                });

                wrapperRotation.start();
                buttonRotation.start();

            }
        } else {
            Log.d(TAG, "Angle was 0; no rotation necessary.");
        }
    }

}
