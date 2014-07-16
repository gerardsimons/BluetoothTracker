package com.devriesdev.whereatassettracking.app;

import android.app.Activity;
import android.graphics.Typeface;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;

/**
 * Created by danie_000 on 6/7/2014.
 */
public class DeviceArrayAdapter extends ArrayAdapter<Object[]> {
    private final Activity context;
    private final ArrayList<Object[]> devices;
    private final ArrayList<String> macAddresses;
    private final int rowLayoutId;

    static class ViewHolder {
        public TextView macText;
        public TextView nameText;
        public TextView rssiText;
        public TextView timeText;
        public TextView updatesText;
    }

    public DeviceArrayAdapter(Activity context, int rowLayoutId,
                              ArrayList<Object[]> devices) {
        super(context, rowLayoutId, devices);
        this.context = context;
        this.devices = devices;
        this.rowLayoutId = rowLayoutId;
        macAddresses = new ArrayList<String>();
    }

    @Override
    public View getView(final int position, View convertView, ViewGroup parent) {
        View rowView = convertView;

        final Object[] device = devices.get(position);

        // Reuse vies
        if (rowView == null) {
            LayoutInflater inflater = context.getLayoutInflater();
            rowView = inflater.inflate(rowLayoutId, null);
            // Configure view holder
            ViewHolder viewHolder = new ViewHolder();
            viewHolder.macText = (TextView) rowView.findViewById(R.id.macText);
            viewHolder.nameText = (TextView) rowView.findViewById(R.id.nameText);
            viewHolder.rssiText = (TextView) rowView.findViewById(R.id.rssiText);
            viewHolder.timeText = (TextView) rowView.findViewById(R.id.timeText);
            viewHolder.updatesText = (TextView) rowView.findViewById(R.id.updatesText);
            rowView.setTag(viewHolder);

            rowView.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    device[4] = ((Boolean)device[4]) ? false : true;
                    devices.remove(position);
                    macAddresses.remove(position);
                }
            });
        }

        // Fill data
        ViewHolder holder = (ViewHolder) rowView.getTag();
        if ((Boolean) device[4]) {
            holder.macText.setTypeface(null, Typeface.BOLD);
            holder.nameText.setTypeface(null, Typeface.BOLD);
            holder.rssiText.setTypeface(null, Typeface.BOLD);
            holder.timeText.setTypeface(null, Typeface.BOLD);
            holder.updatesText.setTypeface(null, Typeface.BOLD);
        } else {
            holder.macText.setTypeface(null, Typeface.NORMAL);
            holder.nameText.setTypeface(null, Typeface.NORMAL);
            holder.rssiText.setTypeface(null, Typeface.NORMAL);
            holder.timeText.setTypeface(null, Typeface.NORMAL);
            holder.updatesText.setTypeface(null, Typeface.NORMAL);
        }
        SimpleDateFormat sdf = new SimpleDateFormat("dd MMM yyyy, HH:mm:ss");
        Date date = new Date((Long)device[2]);
        String dateStr = sdf.format(date);

        holder.macText.setText(macAddresses.get(position));
        holder.nameText.setText((String) device[0]);
        holder.rssiText.setText(String.valueOf((int)Math.round((Double)device[1])));
        holder.timeText.setText(dateStr);
        holder.updatesText.setText(String.valueOf(device[3]));

        return rowView;
    }

    public void processDevice(String macAddress, String name, int rssi) {
        double damping = 0.01;

        int deviceId = macAddresses.indexOf(macAddress);
        long time = System.currentTimeMillis();
        if (deviceId == -1) {
            macAddresses.add(macAddress);
            Object[] device = {name, Double.valueOf(rssi), time, 0, false};
            devices.add(device);
        } else {
            Object[] device = devices.get(deviceId);
            if (!(Boolean)device[4]) {
                device[1] = (Double)device[1] + damping*(rssi - (Double)device[1]);
                device[2] = time;
                device[3] = (Integer) device[3] + 1;
            }
        }
        notifyDataSetChanged();
    }

    public void reset() {
        macAddresses.clear();
        devices.clear();
        notifyDataSetChanged();
    }
}
