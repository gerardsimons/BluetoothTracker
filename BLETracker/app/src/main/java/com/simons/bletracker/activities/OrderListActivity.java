package com.simons.bletracker.activities;

import android.app.ListActivity;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.CheckBox;
import android.widget.TextView;

import com.simons.bletracker.R;
import com.simons.bletracker.models.Order;
import com.simons.bletracker.models.OrderItem;

public class OrderListActivity extends ListActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_order_list);
    }

    private class OrderListAdapter extends BaseAdapter {

        private Order orderList;
        private LayoutInflater mInflator;

        public OrderListAdapter() {
            super();
            mInflator = OrderListActivity.this.getLayoutInflater();
        }

        public int getCount() {
            return orderList.size();
        }

        @Override
        public Object getItem(int i) {
            return orderList.getOrder(i);
        }

        @Override
        public long getItemId(int i) {
            return i;
        }

        @Override
        public View getView(int i, View view, ViewGroup viewGroup) {
            ViewHolder viewHolder;
            // General ListView optimization code.
            if (view == null) {
                view = mInflator.inflate(R.layout.order_list_item, null);
                viewHolder = new ViewHolder();
                viewHolder.checkedInState = (CheckBox) view.findViewById(R.id.checkedInState);
                viewHolder.orderIDText = (TextView) view.findViewById(R.id.orderIDText);

                view.setTag(viewHolder);
            } else {
                viewHolder = (ViewHolder) view.getTag();
            }

            OrderItem order = orderList.getOrder(i);
            final int orderId = order.getID();

//            viewHolder.orderIDText.setText(deviceName);
//
//            viewHolder.deviceAddress.setText(device.getAddress());
//            viewHolder.deviceTemperature.setText(powerLevels.get(i) + "" + (char) 0x00B0 + " C");
            return view;
        }
    }

    static class ViewHolder {
        CheckBox checkedInState;
        TextView orderIDText;
    }
}
