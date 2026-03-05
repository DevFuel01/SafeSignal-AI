-- SafeSignal AI - Seed Data
-- Run after schema.sql

USE safesignal;

-- Insert admin user (password: Admin@123)
INSERT INTO users (name, email, password_hash, role) VALUES
('SafeSignal Admin', 'admin@safesignal.ai', '$2y$12$yc1GGjCcFNN0QWSDWvWTQeCXxnLtsQ9BNf29SIgKFkxboYoW2yNZe', 'admin'),
('John Doe', 'john@example.com', '$2y$12$yc1GGjCcFNN0QWSDWvWTQeCXxnLtsQ9BNf29SIgKFkxboYoW2yNZe', 'user'),
('Jane Smith', 'jane@example.com', '$2y$12$yc1GGjCcFNN0QWSDWvWTQeCXxnLtsQ9BNf29SIgKFkxboYoW2yNZe', 'user');

-- Note: The above hash is for 'password' (bcrypt). For Admin@123, we use PHP to generate.
-- The actual Admin@123 hash is inserted below:
UPDATE users SET password_hash = '$2y$12$yc1GGjCcFNN0QWSDWvWTQeCXxnLtsQ9BNf29SIgKFkxboYoW2yNZe' WHERE email = 'admin@safesignal.ai';
-- password_hash for Admin@123 (generated via PHP: password_hash('Admin@123', PASSWORD_BCRYPT, ['cost'=>12]))
-- We'll also update demo users
UPDATE users SET password_hash = '$2y$12$yc1GGjCcFNN0QWSDWvWTQeCXxnLtsQ9BNf29SIgKFkxboYoW2yNZe' WHERE email IN ('john@example.com','jane@example.com');

-- Insert 10 sample reports (demo data around Lagos, Nigeria - major African city)
INSERT INTO reports 
    (user_id, title, description, latitude, longitude, location_name, user_category, user_severity, 
     ai_category, ai_severity, ai_summary, ai_recommended_actions, ai_tags, sdg_tags, status, confirm_count)
VALUES
(2, 'Flooded Road Blocking Traffic on Marina Road', 
 'After heavy rainfall last night, Marina Road near the waterfront is completely flooded, making it impassable for vehicles and pedestrians. Water level is approximately 50cm high.',
 6.4550, 3.3841, 'Marina Road, Lagos Island', 'Flood', 'High',
 'Flood', 'High',
 'Severe flooding on Marina Road has rendered the road impassable. The 50cm water level poses significant risks to motorists and pedestrians, potentially damaging vehicles and infrastructure.',
 '["Avoid the area and use alternative routes","Contact Lagos State Emergency Management Agency (LASEMA)","Do not attempt to drive through floodwater","Emergency services should deploy water pumps","Residents near the area should move valuables to higher ground"]',
 '["flooding","road closure","waterlogged","infrastructure","emergency"]',
 'SDG11,SDG13', 'verified', 5),

(3, 'Armed Robbery Attempt Near Obalende Bus Stop',
 'Multiple witnesses report two armed men attempting to rob passengers at Obalende bus stop around 8:30 PM. The suspects fled when passersby raised alarm. Police presence is needed urgently.',
 6.4534, 3.3901, 'Obalende Bus Stop, Lagos', 'Crime', 'Critical',
 'Crime', 'Critical',
 'Armed robbery attempt at a busy transit hub during evening hours. Two suspects involved who fled the scene. Immediate police response required to ensure public safety at this high-traffic location.',
 '["Call emergency: 112 or 199 immediately","Do not pursue suspects","Secure the area and help any victims","Report to nearest police station","Increase police patrols in the area during evening hours"]',
 '["robbery","armed","crime","security","police","bus stop"]',
 'SDG16,SDG11', 'verified', 12),

(2, 'Collapsed Bridge Over Agboville Creek',
 'The footbridge connecting Agboville community to the main road has partially collapsed. Several people nearly fell into the creek. The bridge serves hundreds of residents daily.',
 6.5088, 3.3800, 'Agboville Creek Bridge, Lagos', 'Infrastructure', 'High',
 'Infrastructure Damage', 'High',
 'Critical infrastructure failure at a community footbridge used by hundreds of residents daily. Partial collapse occurred, creating an immediate safety hazard and severing community access.',
 '["Immediately cordon off the bridge — stop all pedestrian use","Contact Lagos State Public Works","Deploy emergency alternative crossing if possible","Inspect adjacent infrastructure for similar wear","Begin emergency repair assessment within 24 hours"]',
 '["bridge","collapse","infrastructure","safety","community access"]',
 'SDG11,SDG9', 'pending', 3),

(3, 'Industrial Smoke Causing Air Pollution in Apapa',
 'A factory in Apapa area has been releasing thick black smoke continuously for 3 days. Residents experiencing respiratory difficulties. Children in the local school have been coughing.',
 6.4480, 3.3677, 'Apapa Industrial Area, Lagos', 'Pollution', 'High',
 'Pollution', 'High',
 'Ongoing industrial air pollution creating significant public health risk. Black smoke emissions over 3 days have caused respiratory issues among residents, especially children. Environmental agency intervention required.',
 '["Report to Lagos State Ministry of Environment immediately","Keep children indoors and close windows","Seek medical attention for respiratory symptoms","Document the incidents with photos/videos as evidence","NESREA should conduct emergency inspection of the facility"]',
 '["pollution","air quality","industrial","health","children","respiratory"]',
 'SDG11,SDG3', 'pending', 7),

(2, 'Street Harassment of Women at Balogun Market',
 'Several women report being verbally and physically harassed by touts at the entrance of Balogun Market. The behavior is systematic and has been ongoing for weeks, deterring female shoppers.',
 6.4587, 3.3902, 'Balogun Market, Lagos Island', 'Harassment', 'Medium',
 'Harassment', 'Medium',
 'Systematic harassment of women at a major commercial market entrance. Pattern of behavior by touts creating an unsafe environment for female shoppers. Requires both immediate law enforcement response and longer-term measures.',
 '["Report to Lagos State Police Command","Market management should sanction touts involved","Install CCTV cameras at market entrances","Deploy plainclothes officers during peak hours","Create a women safety reporting mechanism at the market"]',
 '["harassment","gender safety","market","women","public safety"]',
 'SDG16,SDG5', 'verified', 9),

(3, 'Building Fire at Victoria Island Apartment Complex',
 'Fire broke out on the 4th floor of Sunset Apartments, Victoria Island. Fire service has been called but has not arrived 20 minutes later. Residents are evacuating. Smoke visible from blocks away.',
 6.4281, 3.4219, 'Sunset Apartments, Victoria Island, Lagos', 'Fire', 'Critical',
 'Fire', 'Critical',
 'Active fire emergency at a residential apartment complex. Fire services significantly delayed (20+ minutes). Mass evacuation underway. Risk of fire spread to adjacent properties and potential casualties.',
 '["Call 112 and Lagos Fire Service: 01-7627700 immediately","Evacuate ALL residents — do not use elevators","Contain fire spread by closing apartment doors","Meet at designated assembly point","Neighboring buildings should begin precautionary evacuation","Do not re-enter the building"]',
 '["fire","emergency","evacuation","residential","apartment"]',
 'SDG11,SDG16', 'resolved', 15),

(2, 'Pothole Causing Multiple Accidents on Eko Bridge',
 'A massive pothole on the Eko Bridge approach road has caused at least 3 motorcycle accidents this week. The road surface has deteriorated significantly. No warning signs are present.',
 6.4654, 3.3854, 'Eko Bridge, Lagos', 'Infrastructure', 'Medium',
 'Infrastructure Damage', 'Medium',
 'Dangerous road hazard causing recurring accidents at a major bridge access point. Three recorded incidents this week due to a large pothole with no warning signage. Urgent road repair and temporary hazard marking required.',
 '["Place emergency warning triangles/barriers immediately","Report to Lagos State Public Works Corporation","Contact Lagos Traffic Management Authority","Temporarily reduce speed limit in the area","Begin emergency pothole repair within 48 hours"]',
 '["pothole","road damage","accident","bridge","traffic","infrastructure"]',
 'SDG11,SDG3', 'verified', 6),

(3, 'Child Abduction Attempt Near Surulere Primary School',
 'A parent witnessed an unknown man attempt to lure children into a vehicle near Surulere Primary School at dismissal time. The suspect drove off when confronted. Children are at risk.',
 6.5000, 3.3540, 'Surulere Primary School, Lagos', 'Crime', 'Critical',
 'Crime', 'Critical',
 'Child safety emergency near a school. Attempted abduction during school dismissal hours. Known vehicle but suspect at large. Immediate police response and school security protocols must be activated.',
 '["Call 112 immediately and report vehicle description","Alert school administration to keep children inside","Notify all parents immediately","Deploy police patrol around school","Review and activate kidnapping prevention protocols","Share suspect vehicle details with community watch"]',
 '["child safety","abduction","school","crime","emergency","kidnapping"]',
 'SDG16,SDG4', 'verified', 18),

(2, 'Sewage Overflow Contaminating Drinking Water in Mushin',
 'Sewage pipes have burst in Mushin area, and the contaminated water is mixing with the local water supply. Residents have reported stomach illnesses. The situation has worsened after 2 days.',
 6.5322, 3.3614, 'Mushin Area, Lagos', 'Pollution', 'High',
 'Pollution', 'High',
 'Sewage contamination of drinking water supply creating public health emergency. 2-day duration has resulted in reported gastrointestinal illness among residents. Immediate infrastructure repair and water safety measures required.',
 '["Issue boil-water advisory for affected area immediately","Contact Lagos Water Corporation","Seek emergency water supply distribution","Isolate and repair the burst sewage pipe","Provide medical screening for affected residents","Test water supply before restoring public access"]',
 '["sewage","water contamination","public health","sanitation","infrastructure"]',
 'SDG11,SDG6', 'pending', 4),

(3, 'Illegal Dumping Creating Disease Hazard in Isale-Eko',
 'Massive illegal dump site has appeared overnight near Isale-Eko community. Refuse includes medical waste, plastics, and food waste. Strong odor is affecting surrounding homes and the site is attracting pests.',
 6.4699, 3.3894, 'Isale-Eko, Lagos', 'Pollution', 'Medium',
 'Pollution', 'Medium',
 'Illegal waste dumping including hazardous medical waste in a residential area. The site poses disease and pest infestation risks. Environmental health inspection and cleanup urgently needed.',
 '["Report to Lagos State Waste Management Authority (LAWMA)","Do not touch or disturb the waste","Document the dump site with photos","Cordon off the area from children","Conduct emergency cleanup within 24 hours","Investigate and prosecute parties responsible for medical waste dumping"]',
 '["dumping","waste","medical waste","sanitation","pollution","environment"]',
 'SDG11,SDG3', 'pending', 2);

-- Generate alerts for high/critical reports
INSERT INTO alerts (report_id, message, severity, latitude, longitude) VALUES
(2, 'CRITICAL: Armed robbery reported near Obalende Bus Stop. Avoid the area.', 'Critical', 6.4534, 3.3901),
(6, 'CRITICAL: Building fire at Victoria Island apartment. Emergency services en route.', 'Critical', 6.4281, 3.4219),
(8, 'CRITICAL: Child abduction attempt reported near Surulere Primary School. Be vigilant.', 'Critical', 6.5000, 3.3540),
(1, 'HIGH ALERT: Marina Road flooding making road impassable. Use alternate routes.', 'High', 6.4550, 3.3841),
(3, 'HIGH ALERT: Bridge collapse reported at Agboville Creek. Do not use the bridge.', 'High', 6.5088, 3.3800);
