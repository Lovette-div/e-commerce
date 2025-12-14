<!-- import React, { useEffect, useState } from 'react';

// CustomerDashboard.jsx
// React dashboard for The Launder E-Commerce Platform
// Focused on connecting customers to laundry suppliers

export default function CustomerDashboard() {
  const [profile, setProfile] = useState(null);
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('overview');
  const [error, setError] = useState(null);

  useEffect(() => {
    async function load() {
      try {
        setLoading(true);
        const [pRes, oRes] = await Promise.all([
          fetch('/api/customer/profile').then(r => r.json()),
          fetch('/api/customer/orders').then(r => r.json())
        ]);
        if (!pRes.status) throw new Error(pRes.message || 'Failed to load profile');
        if (!oRes.status) throw new Error(oRes.message || 'Failed to load orders');
        setProfile(pRes.customer);
        setOrders(oRes.orders);
      } catch (err) {
        console.error(err);
        setError(err.message || 'Failed to load dashboard');
      } finally {
        setLoading(false);
      }
    }
    load();
  }, []);

  function formatDate(ts) {
    try { return new Date(ts).toLocaleString(); } catch { return ts; }
  }

  async function requestPickup() {
    alert('Pickup request feature coming soon.');
  }

  async function logout() {
    await fetch('/api/auth/logout', { method: 'POST' });
    window.location.href = '/login';
  }

  return (
    <div className="min-h-screen bg-gradient-to-r from-blue-600 to-indigo-500 p-6">
      <div className="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
        {/* Header */}
        <div className="flex items-center justify-between p-6 border-b">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-700 font-bold text-2xl">L</div> -->
            <div>
              <h1 className="text-2xl font-semibold">Welcome{profile ? `, ${profile.name}` : ''}</h1>
              <p className="text-sm text-gray-500">Dashboard — The Launder</p>
            </div>
          </div>
          <div className="flex items-center gap-4">
            <button onClick={requestPickup} className="px-5 py-2 bg-indigo-600 text-white rounded-lg shadow hover:opacity-95">Request Pickup</button>
            <button onClick={logout} className="px-4 py-2 border rounded-lg">Logout</button>
          </div>
        </div>

        <div className="p-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
          {/* Profile & Quick Info */}
          <aside className="lg:col-span-1">
            <div className="p-4 rounded-lg border bg-gray-50">
              <div className="flex items-center gap-4">
                <div className="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xl">{profile ? profile.name.charAt(0) : 'C'}</div>
                <div>
                  <div className="text-lg font-semibold">{profile?.name || 'Customer Name'}</div>
                  <div className="text-sm text-gray-500">{profile?.email || 'email@example.com'}</div>
                </div>
              </div>

              <div className="mt-4">
                <h3 className="text-sm text-gray-600">Account</h3>
                <ul className="mt-2 text-sm space-y-2">
                  <li className="flex justify-between"><span>City</span><strong>{profile?.city || '-'}</strong></li>
                  <li className="flex justify-between"><span>Phone</span><strong>{profile?.contact || '-'}</strong></li>
                  <li className="flex justify-between"><span>Joined</span><strong>{profile ? new Date(profile.created_at).toLocaleDateString() : '-'}</strong></li>
                </ul>
              </div>
            </div>

            <div className="mt-4 p-4 rounded-lg border bg-white">
              <h4 className="font-semibold mb-2">Quick actions</h4>
              <button onClick={requestPickup} className="w-full text-left px-4 py-2 rounded-md border mb-2">Request Pickup</button>
              <a href="/orders/new" className="w-full block px-4 py-2 rounded-md border text-center">Place Order</a>
            </div>
          </aside>

          {/* Main Area */}
          <main className="lg:col-span-3">
            <div className="mb-4 flex items-center gap-4">
              <nav className="flex gap-2 bg-gray-100 rounded-md p-1">
                {['overview','orders','wallet','settings'].map(tab => (
                  <button key={tab} onClick={() => setActiveTab(tab)} className={`px-3 py-1 rounded ${activeTab===tab ? 'bg-white shadow' : ''}`}>{tab.charAt(0).toUpperCase()+tab.slice(1)}</button>
                ))}
              </nav>
              <div className="ml-auto text-sm text-gray-600">{loading ? 'Loading...' : `${orders.length} orders`}</div>
            </div>

            {error && <div className="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">{error}</div>}

            {/* Overview */}
            {activeTab === 'overview' && (
              <section>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                  <Stat title="Active Orders" value={orders.filter(o=>o.status==='processing').length}/>
                  <Stat title="Pending Pickup" value={orders.filter(o=>o.status==='pending_pickup').length}/>
                  <Stat title="Total Spent" value={`GHS ${orders.reduce((s,o)=>s+(o.total||0),0).toFixed(2)}`}/>
                </div>

                <div className="p-4 rounded-lg border bg-white">
                  <h3 className="font-semibold mb-2">Upcoming Pickups</h3>
                  {orders.filter(o=>o.status==='pending_pickup').length===0 ? (
                    <div className="text-sm text-gray-500">No pickups scheduled</div>
                  ) : (
                    <ul className="space-y-2">
                      {orders.filter(o=>o.status==='pending_pickup').map(o=>(
                        <li key={o.order_id} className="p-3 border rounded">
                          <div className="flex justify-between"><div>Order #{o.order_id}</div><div>{formatDate(o.pickup_at)}</div></div>
                          <div className="text-sm text-gray-600">{o.items?.length || 0} items • GHS {o.total}</div>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              </section>
            )}

            {/* Orders */}
            {activeTab === 'orders' && (
              <section>
                <div className="p-4 rounded-lg border bg-white">
                  <h3 className="font-semibold mb-3">Your Orders</h3>
                  <div className="overflow-x-auto">
                    <table className="w-full text-left">
                      <thead>
                        <tr className="text-sm text-gray-500"><th>Order</th><th>Status</th><th>Items</th><th>Total</th><th>Date</th></tr>
                      </thead>
                      <tbody>
                        {orders.length>0?orders.map(o=>(
                          <tr key={o.order_id} className="border-t">
                            <td className="py-3">#{o.order_id}</td>
                            <td className="py-3">{o.status}</td>
                            <td className="py-3">{o.items?.length||0}</td>
                            <td className="py-3">GHS {o.total}</td>
                            <td className="py-3">{formatDate(o.created_at)}</td>
                          </tr>
                        )):(<tr><td colSpan={5} className="p-4 text-center text-gray-500">No orders yet</td></tr>)}
                      </tbody>
                    </table>
                  </div>
                </div>
              </section>
            )}

            {/* Wallet */}
            {activeTab === 'wallet' && (
              <section>
                <div className="p-4 rounded-lg border bg-white">
                  <h3 className="font-semibold mb-3">Wallet</h3>
                  <div className="flex justify-between items-center">
                    <div>
                      <div className="text-sm text-gray-500">Balance</div>
                      <div className="text-2xl font-bold">GHS 120.00</div>
                    </div>
                    <button className="px-4 py-2 border rounded">Top up</button>
                  </div>
                </div>
              </section>
            )}

            {/* Settings */}
            {activeTab === 'settings' && (
              <section>
                <div className="p-4 rounded-lg border bg-white">
                  <h3 className="font-semibold mb-3">Profile Settings</h3>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Input label="Full Name" defaultValue={profile?.name}/>
                    <Input label="Email" defaultValue={profile?.email}/>
                    <Input label="Phone" defaultValue={profile?.contact}/>
                    <Input label="City" defaultValue={profile?.city}/>
                  </div>
                  <div className="mt-4">
                    <button className="px-4 py-2 bg-indigo-600 text-white rounded">Save Changes</button>
                  </div>
                </div>
              </section>
            )}

          </main>
        </div>
      </div>
    </div>
  );
}

function Stat({ title, value }) {
  return (
    <div className="p-4 rounded-lg border bg-white">
      <div className="text-sm text-gray-500">{title}</div>
      <div className="text-2xl font-bold">{value}</div>
    </div>
  );
}

function Input({ label, defaultValue }) {
  return (
    <div>
      <label className="text-sm text-gray-600">{label}</label>
      <input className="w-full mt-1 p-2 border rounded" defaultValue={defaultValue}/>
    </div>
  );
}
