/******************************************************************************
 *
 *  Copyright 2014 Paphus Solutions Inc.
 *
 *  Licensed under the Eclipse Public License, Version 1.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.eclipse.org/legal/epl-v10.html
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 ******************************************************************************/
package org.botlibre.test;

import java.util.List;

import org.botlibre.Bot;
import org.botlibre.sense.text.TextEntry;
import org.botlibre.thought.language.Language;
import org.botlibre.thought.language.Language.LearningMode;
import org.botlibre.util.Utils;
import org.junit.BeforeClass;

/**
 * Test importing words from Wiktionary.
 */

public class TestWiktionary extends TextTest {
	
	public static int SLEEP = 5000;

	@BeforeClass
	public static void setup() {
		bootstrap();
	}

	@org.junit.Test
	public void testWords() {
		Bot bot = Bot.createInstance();
		Language language = bot.mind().getThought(Language.class);
		language.setLearningMode(LearningMode.Disabled);
		TextEntry text = bot.awareness().getSense(TextEntry.class);
		List<String> output = registerForOutput(text);

		text.input("define sky");
		String response = waitForOutput(output);
		if (!response.equals("The atmosphere above a given point, especially as visible from the ground during the day.")) {
			fail("Incorrect: " + response);
		}
		
		text.input("define blue");
		response = waitForOutput(output);
		if (!response.equals("Of the colour blue.")) {
			fail("Incorrect: " + response);
		}
		
		text.input("define like");
		response = waitForOutput(output);
		if (!response.equals("To please.")) {
			fail("Incorrect: " + response);
		}

		text.input("is sky a thing?");
		response = waitForOutput(output);
		assertTrue(response);

		text.input("is blue a thing?");
		response = waitForOutput(output);
		assertTrue(response);
		
		text.input("is sky blue?");
		response = waitForOutput(output);
		assertUnknown(response);
		
		text.input("the sky is blue");
		response = waitForOutput(output);
		assertKnown(response);
		
		text.input("is sky blue?");
		response = waitForOutput(output);
		assertTrue(response);
		
		text.input("I like blue");
		response = waitForOutput(output);
		assertKnown(response);
		
		text.input("Do I like blue?");
		response = waitForOutput(output);
		assertTrue(response);
		
		text.input("what do I like?");
		response = waitForOutput(output);
		assertKeyword(response, "blue");
		
		text.input("what is the time?");
		response = waitForOutput(output);
		if (response.equals("what is the time?")) {
			fail("did not understand time");
		}
		
		text.input("what is the date?");
		response = waitForOutput(output);
		if (response.equals("what is the date?")) {
			fail("did not understand date");
		}

		bot.shutdown();
	}
}

